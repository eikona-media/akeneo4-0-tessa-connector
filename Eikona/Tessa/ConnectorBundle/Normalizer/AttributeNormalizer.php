<?php
/**
 * AttributeNormalizer.php
 *
 * @author      Timo Müller <t.mueller@eikona-media.de>
 * @copyright   2020 EIKONA Media (https://eikona-media.de)
 */

namespace Eikona\Tessa\ConnectorBundle\Normalizer;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Eikona\Tessa\ConnectorBundle\AttributeType\TessaType;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Class AttributeNormalizer
 *
 * @package Eikona\Tessa\ConnectorBundle\Normalizer
 */
class AttributeNormalizer implements NormalizerInterface
{
    /** @var NormalizerInterface */
    protected $normalizer;

    /**
     * @param NormalizerInterface $normalizer
     */
    public function __construct(NormalizerInterface $normalizer)
    {
        $this->normalizer = $normalizer;
    }

    /**
     * @param AttributeInterface $attribute
     * @param null|string        $format
     * @param array              $context
     *
     * @return array
     */
    public function normalize($attribute, $format = null, array $context = [])
    {
        return $this->normalizer->normalize($attribute, $format, $context)
            + $this->getTessaProperties($attribute);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $this->normalizer->supportsNormalization($data, $format);
    }

    private function getTessaProperties(AttributeInterface $attribute)
    {
        $properties = [];

        $productsOnly = $attribute->getProperty(TessaType::ATTRIBUTE_CDN_URL);
        $properties[TessaType::ATTRIBUTE_CDN_URL] = $productsOnly;

        return $properties;
    }
}
