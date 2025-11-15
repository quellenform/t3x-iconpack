<?php

declare(strict_types=1);

namespace Quellenform\Iconpack\Controller;

/*
 * This file is part of the "iconpack" Extension for TYPO3 CMS.
 *
 * Conceived and written by Stephan Kellermayr
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Quellenform\Iconpack\IconpackFactory;
use Quellenform\Iconpack\Utility\IconpackRenderer;
use Quellenform\Iconpack\Utility\IconpackUtility;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Core\View\ViewFactoryData;
use TYPO3\CMS\Core\View\ViewFactoryInterface;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Query iconpack data for the Backend.
 */
class IconpackController
{
    /**
     * @var IconpackFactory
     */
    protected $iconpackFactory;

    /**
     * Iconpack configuration.
     *
     * @var array|null
     */
    protected $iconfig = null;

    /**
     * Query data for Modal view.
     *
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function getModalAction(ServerRequestInterface $request): ResponseInterface
    {
        $this->initialize($request);

        if (!$this->iconpackFactory->areThereAnyIconpacksInstalled(true)) {
            $html = $this->renderStandaloneView($request, 'NoIconpack', []);
        } else {
            $styles = $this->iconpackFactory->queryIconpackStyles($this->iconfig['fieldType']);
            if (
                isset($this->iconfig['iconpack']) &&
                $this->iconpackFactory->isIconpackInstalled($this->iconfig['iconpack'])
            ) {
                // Select an option if iconpack is already defined
                $this->setSelectedOption(
                    $styles,
                    $this->iconfig['iconpackStyle']
                );
            } else {
                // Otherwise set the first available iconpack style as selected
                $this->iconfig = IconpackUtility::convertIconfigToArray(
                    $this->iconfig['fieldType'],
                    array_key_first($styles)
                );
            }
            $settings = [
                'iconfig' => $this->iconfig,
                'iconpackStyles' => $styles,
                'iconpackOptions' => $this->iconpackFactory->queryIconpackOptions($this->iconfig['iconpack']),
                'iconpackIcons' => $this->iconpackFactory->queryIconpackIcons($this->iconfig),
                'icon' => IconpackRenderer::renderIcon($this->iconpackFactory->getIconElement($this->iconfig)),
                'modalStylesheets' => \json_encode($this->iconpackFactory->queryAssets('css', 'backend', true))
            ];
            $html = $this->renderStandaloneView($request, 'Iconpack', $settings);
        }
        return new HtmlResponse($html);
    }

    /**
     * Query data for Modal updates.
     *
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function updateModalAction(ServerRequestInterface $request): ResponseInterface
    {
        $this->initialize($request);
        $settings = [];
        if ($this->iconfig) {
            $settings = [
                'iconpackOptions' => $this->renderStandaloneView(
                    $request,
                    'Options',
                    [
                        'iconpackOptions' => $this->iconpackFactory->queryIconpackOptions(
                            $this->iconfig['iconpack']
                        ),
                    ],
                    true
                ),
                'iconpackIcons' => $this->renderStandaloneView(
                    $request,
                    'Icons',
                    [
                        'iconpackIcons' => $this->iconpackFactory->queryIconpackIcons(
                            $this->iconfig
                        )
                    ],
                    true
                )
            ];
        }
        return new JsonResponse($settings);
    }

    /**
     * Query Icon for the final output in the Backend form and in CKEditor.
     *
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function getIconAction(ServerRequestInterface $request): ResponseInterface
    {
        $this->initialize($request);
        $settings = [];
        if ($this->iconfig) {
            $settings = [
                'icon' => $this->iconpackFactory->getIconElement(
                    $this->iconfig
                )
            ];
        }
        return new JsonResponse($settings);
    }

    /**
     * Initialize IconpackFactory with required parameters.
     *
     * @param ServerRequestInterface $request
     *
     * @return void
     */
    public function initialize(ServerRequestInterface $request)
    {
        if (!$this->iconpackFactory) {
            $context
                = !empty($request->getQueryParams()['context'])
                ? $request->getQueryParams()['context']
                : 'backend';
            $this->iconpackFactory = GeneralUtility::makeInstance(IconpackFactory::class);
            $this->iconpackFactory->setContext($context);
        }

        $this->iconfig = $this->iconpackFactory->substituteIconpackInIconfigArray(
            IconpackUtility::convertIconfigToArray(
                $request->getQueryParams()['fieldType'] ?? 'native',
                $request->getQueryParams()['iconfig'] ?? null
            )
        );
    }

    /**
     * Set an dropdown-option as 'selected' by given key.
     *
     * @param array $data
     * @param string $selected
     *
     * @return void
     */
    public function setSelectedOption(array &$data, string $selected)
    {
        foreach ($data as $key => $_) {
            if ($selected === $key) {
                $data[$key]['selected'] = true;
            }
        }
    }

    /**
     * Render output for the iconpack modal.
     *
     * @param ServerRequestInterface $request
     * @param string $templateName
     * @param array $settings
     * @param bool $partial
     *
     * @return string
     */
    protected function renderStandaloneView(
        ServerRequestInterface $request,
        string $templateName,
        array $settings,
        bool $partial = false
    ): string {
        if (version_compare(VersionNumberUtility::getCurrentTypo3Version(), '13.0.0', '>=')) {
            $viewFactory = GeneralUtility::makeInstance(ViewFactoryInterface::class);
            $templateRootPaths = ['EXT:iconpack/Resources/Private/Templates/Backend/'];
            $partialRootPaths = ['EXT:iconpack/Resources/Private/Partials/Backend/'];
            if ($partial) {
                $templateRootPaths = ['EXT:iconpack/Resources/Private/Partials/Backend/'];
                $partialRootPaths = null;
            }
            $viewFactoryData = new ViewFactoryData(
                $templateRootPaths,
                $partialRootPaths,
                null,
                null,
                $request,
                'html'
            );
            $view = $viewFactory->create($viewFactoryData);
            $view->assignMultiple($settings);
            return $view->render($templateName);
        } else {
            // @extensionScannerIgnoreLine
            /** @var StandaloneView $standaloneView */
            $standaloneView = GeneralUtility::makeInstance(StandaloneView::class);
            $standaloneView->setFormat('html');
            if ($partial) {
                $path = 'EXT:iconpack/Resources/Private/Partials/Backend/' . $templateName . '.html';
                $standaloneView->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName($path));
            } else {
                $standaloneView->setTemplate($templateName);
                $standaloneView->setTemplateRootPaths([
                    'EXT:iconpack/Resources/Private/Templates/Backend/'
                ]);
                $standaloneView->setPartialRootPaths([
                    'EXT:iconpack/Resources/Private/Partials/Backend/'
                ]);
            }
            $standaloneView->assignMultiple($settings);
            return $standaloneView->render();
        }
    }
}
