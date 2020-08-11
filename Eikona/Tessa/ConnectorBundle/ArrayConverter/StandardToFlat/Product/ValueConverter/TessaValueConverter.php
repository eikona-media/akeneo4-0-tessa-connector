<?php
/**
 * TessaValueConverter.php
 *
 * @author    Matthias Mahler <m.mahler@eikona.de>
 * @copyright 2017 Eikona AG (http://www.eikona.de)
 */

namespace Eikona\Tessa\ConnectorBundle\ArrayConverter\StandardToFlat\Product\ValueConverter;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AttributeColumnsResolver;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\StandardToFlat\Product\ValueConverter\AbstractValueConverter;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\StandardToFlat\Product\ValueConverter\ValueConverterInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\CachedObjectRepositoryInterface;
use Eikona\Tessa\ConnectorBundle\Security\AuthGuard;
use Eikona\Tessa\ConnectorBundle\Tessa;

class TessaValueConverter extends AbstractValueConverter implements ValueConverterInterface
{
    /**
     *
     * @var array
     */
    private $tessa;

    /**
     * @var CachedObjectRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var AuthGuard
     */
    private $authGuard;

    /**
     * TessaValueConverter constructor.
     *
     * @param AttributeColumnsResolver        $columnsResolver
     * @param CachedObjectRepositoryInterface $attributeRepository
     * @param Tessa                           $tessa
     * @param AuthGuard                       $authGuard
     */
    public function __construct(
        AttributeColumnsResolver $columnsResolver,
        CachedObjectRepositoryInterface $attributeRepository,
        Tessa $tessa,
        AuthGuard $authGuard
    )
    {
        $this->attributeRepository = $attributeRepository;
        $this->tessa = $tessa;
        $this->authGuard = $authGuard;
        parent::__construct($columnsResolver, ['eikona_catalog_tessa']);
    }

    /**
     * Converts a value
     *
     * @param string $attributeCode
     * @param mixed  $data
     *
     * @return array
     */
    public function convert($attributeCode, $data)
    {
        $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);
        $convertedItem = [];

        foreach ($data as $value) {
            $flatName = $this->columnsResolver->resolveFlatAttributeName(
                $attributeCode,
                $value['locale'],
                $value['scope']
            );

            $convertedItem[$flatName] = $this->convertAssetIdsToUrls(
                $attribute,
                $value['data'],
                $value['scope']
            );
        }

        return $convertedItem;
    }

    /**
     * @param AttributeInterface $attribute
     * @param string             $data
     * @param string             $scope
     *
     * @return string
     */
    private function convertAssetIdsToUrls(AttributeInterface $attribute, $data, $scope)
    {
        if (trim($data) === '') {
            return '';
        }

        $cdnUrl = $attribute->getProperty('tessa_cdn_url');
        $scopeText = $scope !== null ? $scope : 'null';

        $assetIds = explode(',', $data);
        $assetUrls = array_map(function ($assetId) use ($cdnUrl, $scope, $scopeText) {
            if (!empty($cdnUrl)) {
                return str_replace(
                    ['{ASSET_ID}', '{SCOPE}'],
                    [$assetId, $scopeText],
                    $cdnUrl
                );
            }
            return $this->tessa->getBaseUrl() . "/ui/download.php?asset_system_id=$assetId&kanal=$scope&key={$this->authGuard->getDownloadAuthToken($assetId, 'download')}";
        }, $assetIds);


        return implode(';', $assetUrls);
    }
}
