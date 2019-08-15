<?php

namespace DotBlue\Mpdf;

use LogicException;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Mpdf\Mpdf;
use Nette;
use Nette\Application\Application;
use Nette\Utils\Strings;


class DocumentFactory
{

	use Nette\SmartObject;

	/** @var string */
	private $templateDir;

	/** @var array */
	private $defaults = [
		'encoding' => 'utf-8',
		'fonts' => [],
		'img_dpi' => 120,
		'size' => 'A4',
		'margin' => [
			'left' => 0,
			'right' => 0,
			'top' => 0,
			'bottom' => 0,
		],
	];

	/** @var array */
	private $customFonts;

	/** @var array */
	private $customFontsDirs;

	/** @var string|null */
	private $defaultFont;

	/** @var array[] */
	private $themes = [];

	/** @var ITemplateFactory */
	private $templateFactory;

	/** @var Application */
	private $application;



	/**
	 * @param  string
	 * @param  array
	 * @param  array
	 * @param  ITemplateFactory
	 */
	public function __construct($templateDir, array $defaults, array $customFontsDirs, array $customFonts, ?string $defaultFont, ITemplateFactory $templateFactory)
	{
		$this->templateDir = rtrim($templateDir, DIRECTORY_SEPARATOR);
		$this->defaults = array_replace_recursive($this->defaults, $defaults);
		$this->templateFactory = $templateFactory;

		$this->customFonts = $customFonts;
		$this->customFontsDirs = $customFontsDirs;
		$this->defaultFont = $defaultFont;
	}



	/**
	 * Registers new theme.
	 *
	 * @param  string
	 * @param  array
	 */
	public function addTheme($name, array $setup)
	{
		$this->themes[$name] = array_replace_recursive($this->defaults, $setup);
	}



	/**
	 * Creates new PDF.
	 *
	 * @param  string
	 * @param  string|NULL
	 * @param  array|NULL
	 * @return Document
	 */
	public function createPdf($theme, $variant = 'default.latte', array $setup = [])
	{
		$pdf = $this->createThemedMpdf($theme, $setup);

		$themeDir = $this->templateDir . '/' . $theme;

		$template = $this->templateFactory->createTemplate();
		$template->setFile($themeDir . '/' . $variant);
		$template->dir = $themeDir;

		$pdf->SetBasePath($themeDir);

		if (is_file($themeDir . '/style.css')) {
			$pdf->WriteHTML(file_get_contents($themeDir . '/style.css'), 1);
		}

		return new Document($pdf, $template);
	}



	/**
	 * @param  string
	 * @param  array|NULL
	 * @return Mpdf
	 */
	private function createThemedMpdf($theme, array $setup = [])
	{
		if (!isset($this->themes[$theme])) {
			throw new UnknownThemeException("Theme '$theme' isn't registered.");
		}

		$setup = array_replace_recursive($this->themes[$theme], $setup);

		$defaultConfig = (new ConfigVariables())->getDefaults();
		$fontDirs = $defaultConfig['fontDir'];

		$defaultFontConfig = (new FontVariables())->getDefaults();
		$fontData = $defaultFontConfig['fontdata'];

		$mpdf = new Mpdf([
			'fontDir' => array_merge($fontDirs, $this->customFontsDirs),
			'fontdata' => $fontData + $this->customFonts,
			'default_font' => $this->defaultFont,
			'format' => $setup['size'],
			'margin_left' => $setup['margin']['left'],
			'margin_right' => $setup['margin']['right'],
			'margin_top' => $setup['margin']['top'],
			'margin_bottom'=> $setup['margin']['bottom']
		]);
		$mpdf->showImageErrors = TRUE;
		$mpdf->img_dpi = $setup['img_dpi'];
		return $mpdf;
	}

}
