<?php

namespace DotBlue\Mpdf\TemplateFactories;

use DotBlue\Mpdf\ITemplateFactory;
use Nette;
use Nette\Application\UI;


class DefaultLatteTemplateFactory implements ITemplateFactory
{

	use Nette\SmartObject;

	/** @var UI\ITemplateFactory */
	private $templateFactory;



	public function __construct(UI\ITemplateFactory $templateFactory)
	{
		$this->templateFactory = $templateFactory;
	}



	public function createTemplate()
	{
		return $this->templateFactory->createTemplate();
	}

}
