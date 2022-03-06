<?php

class MainPage extends BasePage {
    private $mesageFile; // сообщения которые забрали из бд(в нашем случае из файла)
    function __construct()
    {
        parent::__construct("base title", " base description", true);
        $this->$mesageFile = new MessageDB();
    }

    function buildContent()
    {
        $messages = $this->mesageRepo->getAll();
        return Template::build(
            file_get_contents('./templates/content.tpl'),
            [
                'messages' => $messages
            ]
        );
    }
}