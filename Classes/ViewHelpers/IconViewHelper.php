<?php

declare(strict_types=1);

namespace Quellenform\Iconpack\ViewHelpers;

/*
 * This file is part of the "iconpack" Extension for TYPO3 CMS.
 *
 * Conceived and written by Stephan Kellermayr
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use Closure;
use Quellenform\Iconpack\IconpackFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

/**
 * Render an icon from a fluid template.
 * Example Usage:
 *   <i:icon iconfig="fa5:brands,xbox" additionalAttributes="{style:'color:red'}" preferredRenderTypes="webfont,svgSprite" />
 */
class IconViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    protected $escapeOutput = false;

    public function initializeArguments()
    {
        $this->registerArgument('iconfig', 'string', 'The rendering configuration of the requested icon', true);
        $this->registerArgument('additionalAttributes', 'array', 'Additional attributes', false);
        $this->registerArgument('preferredRenderTypes', 'string', 'Comma separated list of the preferred renderTypes', false);
    }

    /**
     * Render the header icon.
     *
     * @param array $arguments
     * @param Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return string
     */
    public static function renderStatic(
        array $arguments,
        Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ): string {
        /** @var IconpackFactory $iconpackFactory */
        $iconpackFactory = GeneralUtility::makeInstance(IconpackFactory::class);
        return $iconpackFactory->getIconMarkup(
            $arguments['iconfig'],
            'native',
            $arguments['additionalAttributes'],
            $arguments['preferredRenderTypes']
        ) ?? '';
    }
}
