<?php

namespace source;

abstract class AbstractController {
    
}

abstract class PageController extends AbstractController {
    //TODO: return view instead of templating or redirect in subclasses
    //TODO: fix container loading so subclasses don't have to load the parent classes
    public function __construct (protected Template $templating, protected Request $request, array $parameters = []) {
        $this->templating->bind_parameters($parameters);
    }
    
    public function index(array $parameters = []) : string {
        if ($parameters) {
            $this->templating->bind_parameters($parameters);
        }
        return $this->templating->render(new PageBuffer('./public/html' . $this->request->uri() . '.html'));
    }
}
