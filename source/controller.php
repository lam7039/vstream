<?php

namespace source;

abstract class controller {
    
}

abstract class page_controller extends controller {
    //TODO: fix container loading so subclasses don't have to load the parent classes
    public function __construct (protected Template $templating, protected Request $request, array $parameters = []) {
        $this->templating->bind_parameters($parameters);
    }
    
    public function index(array $parameters = []) : string {
        if ($parameters) {
            $this->templating->bind_parameters($parameters);
        }
        return $this->templating->render(new page_buffer('./public/html' . $this->request->uri() . '.html'));
    }
}
