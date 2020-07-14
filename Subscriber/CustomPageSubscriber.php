<?php

namespace BestBuyContentInsert\Subscriber;

use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\TransactionRequiredException;
use Enlight\Event\SubscriberInterface;
use Shopware\Bundle\MediaBundle\MediaService;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Media\Media;
use function array_filter;
use function explode;
use function str_replace;

class CustomPageSubscriber implements SubscriberInterface {

    /**
     * @var string
     */
    private $pluginDirectory;

    /** @var ModelManager */
    private $modelManager;

    /** @var MediaService */
    private $mediaService;

    /**
     * CustomPageSubscriber constructor.
     *
     * @param $pluginDirectory
     * @param \Shopware\Components\Model\ModelManager $modelManager
     */
    public function __construct($pluginDirectory, ModelManager $modelManager, MediaService $mediaService) {
        $this->pluginDirectory = $pluginDirectory;
        $this->modelManager = $modelManager;
        $this->mediaService = $mediaService;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents() {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Frontend' => 'onPostDispatchSecureFrontend',
        ];
    }

    /**
     * @return array
     */
    private function defaultVarsValues() {
        return ['banner_label' => 'Anzeige'];
    }

    /**
     * @param \Shopware\Models\Media\Media $media
     *
     * @return string|null
     */
    private function getMediaUrl(Media $media) {
        $path = $media->getPath();
        return $this->mediaService->getUrl($path);
    }

    /**
     * @param \Enlight_Event_EventArgs $args
     */
    public function onPostDispatchSecureFrontend(\Enlight_Event_EventArgs $args) {

        /** @var \Enlight_Controller_Plugins_ViewRenderer_Bootstrap $subject */
        $subject = $args->getSubject();

        /** @var \Enlight_View_Default $view */
        $view = $subject->View();
        $view->addTemplateDir($this->pluginDirectory . '/Resources/views');

        $sCustomPage = $view->getAssign('sCustomPage');
        $config = $this->getConfig($sCustomPage);

        $replaceContent = '';
        if ($config['active']) {
            $sVars = $this->getVars($sCustomPage, $this->extractVarNames($config['attributes']));
            $this->prepareVars($sVars);
            $view->assign('sVars', $sVars);
            $replaceContent = $view->fetch($config['template']);
        }

        $token = "[[{$config['token']}]]";

        $sContent = str_replace($token, $replaceContent, $view->getAssign('sContent'));
        $view->assign('sContent', $sContent);
    }

    /**
     * Извлекает значения настроек из аттрибутов
     *
     * @param $sCustomPage
     *
     * @return array
     */
    private function getConfig($sCustomPage) {
        $attributes = $sCustomPage['attribute'];

        $fields = ['active', 'token', 'attributes', 'template'];
        $prefix = 'content_insert';

        $config = [];
        foreach ($fields as $field) {
            $config[$field] = $attributes["{$prefix}_{$field}"];
        }

        return $config;
    }

    /**
     * Извлекает значения полей, для передачи в шаблон из атрибутов
     *
     *
     * @param $sCustomPage
     * @param array $names
     *
     * @return mixed
     */
    private function getVars($sCustomPage, array $names) {
        $attributes = $sCustomPage['attribute'];
        $default_values = $this->defaultVarsValues();
        foreach ($names as $name) {
            if (empty($default_values[$name])) {
                continue;
            }
            $attributes[$name] = $default_values[$name];
        }
        return $attributes;
    }

    /**
     * Подготавливает переменные шаблона
     *
     * @param $vars
     */
    private function prepareVars(&$vars) {
        /*
         * Формируем url картинок вместо их идентификаторов
         */
        $media_fields = ['banner_image', 'banner_logo'];
        $media_ids = [];
        foreach ($media_fields as $field) {
            $media_ids[$field] = $vars[$field];
        }
        $media_entities = $this->loadMedia($media_ids);

        foreach ($media_ids as $field => $id) {
            $media = $media_entities[$id];
            $vars[$field] = $this->getMediaUrl($media);
        }

        /**
         * Разбиваем поле banner_content на массив строк, для вывода в виде списка (ul li)
         */
        $vars['banner_content'] = $this->prepareBannerContent($vars['banner_content']);

    }

    /**
     * Разбивает текст с переносом строк на массив строк
     * @param $text
     *
     * @return array
     */
    private function extractVarNames($text) {
        return array_filter(explode("\n", $text));
    }

    /**
     * Разбивает текст с переносом строк на массив строк
     *
     * @note Нa данный момент дублирует метод extractVarNames, возможно может понадобиться другой способ разбивки
     * текста и валидацию
     *
     * @param $content
     *
     * @return array
     */
    private function prepareBannerContent($content) {
        return array_filter(explode("\n", $content));
    }

    /**
     *
     * Загружает картинки(Media) по их идентификаторам
     * @param $ids
     *
     * @return array
     */
    private function loadMedia($ids) {

        /** @var \Shopware\Models\Media\Repository */
        $repository = $this->modelManager->getRepository(Media::class);
        $entities = [];
        try {
            foreach ($repository->findBy(['id' => $ids]) as $model) {
                $entities[$model->getId()] = $model;
            }

        } catch (OptimisticLockException $e) {
        } catch (TransactionRequiredException $e) {
        } catch (ORMException $e) {
        }
        return $entities;
    }

}
