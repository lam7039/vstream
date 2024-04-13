<?php

namespace controllers;

use source\page_controller;
use source\Request;
use source\Template;

//TODO: create a web or pages folder for page_controller classes
class browse extends page_controller {
	public function __construct(Template $templating, Request $request) {
		parent::__construct($templating, $request);
	}
}
