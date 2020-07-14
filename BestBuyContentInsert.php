<?php


namespace BestBuyContentInsert;


use Shopware\Bundle\AttributeBundle\Service\TypeMappingInterface;
use Shopware\Components\Plugin;
use Shopware\Components\Plugin\Context\ActivateContext;
use Shopware\Components\Plugin\Context\DeactivateContext;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin\Context\UninstallContext;

class BestBuyContentInsert extends Plugin {

    const TABLE_CMS_STATIC_ATTRIBUTES = 's_cms_static_attributes';

    private function getAttributesDefault() {
        return [
            'custom' => TRUE,
            'translatable' => FALSE,
            'displayInBackend' => TRUE,
        ];
    }

    private function getAttributesContentInsert() {
        $attributes['content_insert_active'] = [
            'tableName' => self::TABLE_CMS_STATIC_ATTRIBUTES,
            'columnType' => TypeMappingInterface::TYPE_BOOLEAN,
            'position' => 1,
            'options' => [
                'label' => 'Content insert',
                'supportText' => 'Insert custom content to page content',
                'helpText' => 'Insert custom content to page content',
            ],
        ];
        $attributes['content_insert_token'] = [
            'tableName' => self::TABLE_CMS_STATIC_ATTRIBUTES,
            'columnType' => TypeMappingInterface::TYPE_STRING,
            'position' => 2,
            'options' => [
                'label' => 'Token',
                'supportText' => 'Replacement token (without square brackets)',
                'helpText' => 'the token must be wrapped in brackets (for example: "[[TOKEN_NAME]]" ) and inserted into the page text',
            ],
        ];
        $attributes['content_insert_attributes'] = [
            'tableName' => self::TABLE_CMS_STATIC_ATTRIBUTES,
            'columnType' => TypeMappingInterface::TYPE_TEXT,
            'position' => 3,
            'options' => [
                'label' => 'Attributes',
                'supportText' => 'Attribute values for smarty template',
                'helpText' => 'One name per line',

            ],
        ];
        $attributes['content_insert_template'] = [
            'tableName' => self::TABLE_CMS_STATIC_ATTRIBUTES,
            'columnType' => TypeMappingInterface::TYPE_STRING,
            'position' => 4,
            'options' => [
                'label' => 'Template',
                'supportText' => 'Template file name',
                'helpText' => 'Template file name',
            ],
        ];

        return $attributes;
    }

    private function getAttributesBanner() {
        $attributes['banner_label'] = [
            'tableName' => self::TABLE_CMS_STATIC_ATTRIBUTES,
            'columnType' => TypeMappingInterface::TYPE_STRING,
            'position' => 20,
            'options' => [
                'label' => 'Adventorial labeling',
                'supportText' => 'Adventorial labeling',
                'helpText' => 'Adventorial labeling',
            ],
        ];

        $attributes['banner_logo'] = [
            'tableName' => self::TABLE_CMS_STATIC_ATTRIBUTES,
            'columnType' => TypeMappingInterface::TYPE_SINGLE_SELECTION,
            'position' => 21,
            'options' => [
                'label' => 'Banner logo',
                'supportText' => 'Banner logo',
                'helpText' => 'Banner logo',
                'entity' => 'Shopware\Models\Media\Media',
            ],
        ];
        $attributes['banner_image'] = [
            'tableName' => self::TABLE_CMS_STATIC_ATTRIBUTES,
            'columnType' => TypeMappingInterface::TYPE_SINGLE_SELECTION,
            'position' => 22,
            'options' => [
                'label' => 'Banner image',
                'supportText' => 'Banner image',
                'helpText' => 'Banner image',
                'entity' => 'Shopware\Models\Media\Media',
            ],
        ];
        $attributes['banner_top'] = [
            'tableName' => self::TABLE_CMS_STATIC_ATTRIBUTES,
            'columnType' => TypeMappingInterface::TYPE_TEXT,
            'position' => 23,
            'options' => [
                'label' => 'Banner Top',
                'supportText' => 'Banner Top',
                'helpText' => 'Banner Top',

            ],
        ];
        $attributes['banner_content'] = [
            'tableName' => self::TABLE_CMS_STATIC_ATTRIBUTES,
            'columnType' => TypeMappingInterface::TYPE_TEXT,
            'position' => 24,
            'options' => [
                'label' => 'Content',
                'supportText' => '(HTML)',
                'helpText' => '(HTML)',
            ],
        ];

        $attributes['banner_link_label'] = [
            'tableName' => self::TABLE_CMS_STATIC_ATTRIBUTES,
            'columnType' => TypeMappingInterface::TYPE_STRING,
            'position' => 25,
            'options' => [
                'label' => 'Link label',
                'supportText' => 'Link label',
                'helpText' => 'Link label',
            ],
        ];


        $attributes['banner_link_url'] = [
            'tableName' => self::TABLE_CMS_STATIC_ATTRIBUTES,
            'columnType' => TypeMappingInterface::TYPE_STRING,
            'position' => 26,
            'options' => [
                'label' => 'Link URL',
                'supportText' => 'Link URL',
                'helpText' => 'Link URL',
            ],
        ];

        return $attributes;
    }

    private function getAttributes() {
        $attributes = $this->getAttributesContentInsert();
        $attributes += $this->getAttributesBanner();

        foreach (array_keys($attributes) as $column_name) {
            $attributes[$column_name]['columnName'] = $column_name;
        }

        return $attributes;
    }

    public function install(InstallContext $installContext) {

        $service = $this->container->get('shopware_attribute.crud_service');

        $attributes_default = $this->getAttributesDefault();

        foreach ($this->getAttributes() as $attribute_config) {

            $options = $attribute_config['options'] + $attributes_default;

            $service->update($attribute_config['tableName'], $attribute_config['columnName'], $attribute_config['columnType'], [
                    'position' => $attribute_config['position'] + 100,
                ] + $options);
        }
    }

    public function activate(ActivateContext $activateContext) {
        $activateContext->scheduleClearCache(ActivateContext::CACHE_LIST_ALL);
    }

    public function deactivate(DeactivateContext $deactivateContext) {
        $deactivateContext->scheduleClearCache(DeactivateContext::CACHE_LIST_ALL);
    }

    public function uninstall(UninstallContext $uninstallContext) {
        $uninstallContext->scheduleClearCache(UninstallContext::CACHE_LIST_ALL);

        /** @var $service \Shopware\Bundle\AttributeBundle\Service\CrudServiceInterface */
        $service = $this->container->get('shopware_attribute.crud_service');

        foreach ($this->getAttributes() as $attribute_config) {
            if ($service->get($attribute_config['tableName'], $attribute_config['columnName'])) {
                $service->delete($attribute_config['tableName'], $attribute_config['columnName']);
            }
        }
    }
}
