<?php

namespace source;

//TODO: loose singleton in container?
class View {
    private page_buffer $page_buffer;

    //TODO: fix container loading so subclasses don't have to load the parent classes
    public function __construct(protected Template $templating, array $parameters = []) {
        $this->templating->bind_parameters($parameters);
    }

    public function make(string $url, array $parameters = []) : void {
        if ($parameters) {
            $this->templating->bind_parameters($parameters);
        }
        $this->page_buffer = new page_buffer($url);
    }
    
    public function render() : string {
        return $this->templating->render($this->page_buffer);
    }
}

function view(string $url, array $parameters = []) : string {
    //TODO: fetch class with DI container
    // return View::make($url, $parameters);
    return '';
}
