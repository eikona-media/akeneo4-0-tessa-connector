<?php
/**
 * LinkParser.php
 *
 * @author      Timo Müller <t.mueller@eikona-media.de>
 * @copyright   2020 EIKONA Media (https://eikona-media.de)
 */

namespace Eikona\Tessa\ConnectorBundle\Utilities;

use Akeneo\Tool\Component\StorageUtils\Repository\CachedObjectRepositoryInterface;
use Eikona\Tessa\ConnectorBundle\AttributeType\TessaType;

/**
 * Class LinkParser
 *
 * @package Eikona\Tessa\ConnectorBundle\Utilities
 */
class LinkParser
{
    /**
     * @var CachedObjectRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * LinkGenerator constructor.
     *
     * @param CachedObjectRepositoryInterface $attributeRepository
     */
    public function __construct(
        CachedObjectRepositoryInterface $attributeRepository
    )
    {
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @param $url
     *
     * @return bool
     */
    public function isTessaAssetUrl($url): bool
    {
        return is_string($url)
            && strpos($url, '/ui/download.php') !== false
            && strpos($url, 'asset_system_id=') !== false;
    }

    /**
     * @param $assetUrl
     * @param $attributeCode
     *
     * @return int|null
     */
    public function getAssetIdFromCdnUrl($assetUrl, $attributeCode): ?int
    {
        $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);
        $cdnUrl = $attribute->getProperty(TessaType::ATTRIBUTE_CDN_URL);

        if (!empty($cdnUrl)) {
            $regex = str_replace(
                ['{ASSET_ID}', '{SCOPE}'],
                ['PLACEHOLDER_ASSET_ID', 'PLACEHOLDER_SCOPE'],
                $cdnUrl
            );
            $regex = preg_quote($regex, '/');
            $regex = implode('(?P<assetId>\d+)', explode('PLACEHOLDER_ASSET_ID', $regex, 2));
            $regex = str_replace(
                ['PLACEHOLDER_ASSET_ID', 'PLACEHOLDER_SCOPE'],
                ['(\d+)', '([a-zA-Z0-9_]*)'],
                $regex
            );
            $regex = '/' . $regex . '/U';

            if (preg_match($regex, $assetUrl, $matches) !== false) {
                return (int)$matches['assetId'];
            }
        }

        return null;
    }

    /**
     * @param $assetUrl
     *
     * @return int|null
     */
    public function getAssetIdFromTessaUrl($assetUrl): ?int
    {
        if (preg_match('/asset_system_id=(\d+)/', $assetUrl, $matches) !== false) {
            return (int)$matches[1];
        }
        return null;
    }
}
