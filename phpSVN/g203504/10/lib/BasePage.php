<?php

/**
 * Класс основы для страницы и её элементов.
 */
abstract class BasePage {
    private $header;
    private $description;
    private $showMessageForm;

    /**
     * Намётки функции создания кода куска страницы.
     * 
     * @return void 
     */
    abstract public function buildContent();

    /**
     * Конструктор. 
     * 
     * @param string  $header          Голова обьекта.
     * @param string  $description     Низ обьекта.
     * @param boolean $showMessageForm Надо ли его выводить.
     */
    function __construct(string $header, string $description, bools $showMessageForm)
    {
        $this->header = $header;
        $this->description = $description;
        $this->showMessageForm = $showMessageForm ? 1 : 0; // For Template.php
    }

    /**
     * Задание header'а
     * 
     * @param string $header
     */
    public function setHeader ($header) {
        $this->header = $header;
    }

    /**
     * 
     * @param type $description
     */
    public function setDescription ($description) {
        $this->description = $description;
    }

    /**
     * Выводить ли форму.
     * 
     * @param type $value
     */
    public function setShowMessageForm ($value) {
        $this->showMessageForm = $value;
    }

    /**
     * Возвращаем header страницы
     * 
     * @return string
     */
    private function buildHeader()
    {
        return Template::build(
            file_get_contents('./templates/header.tpl'),
            []
        );
    }

    /**
     * Возвращаем footer страницы
     * 
     * @return type
     */
    private function buildFooter()
    {
        return Template::build(
            file_get_contents('./templates/footer.tpl'),
            []
        );
    }

    /**
     * Возвращаем header'ы контентов
     * 
     * @return string
     */
    private function buildContentHeader()
    {
        return Template::build(
            file_get_contents('./templates/content-header.tpl'),
            [
                'header' => $this->header,
                'description' => $this->description,
                'showMessageForm' => $this->showMessageForm
            ]
        );
    }
    
    /**
     *  Вывод всей страницы.
     * 
     * @return void
     */
    public function display()
    {
        $html = implode(
            '',
            array(
                $this->buildHeader(),
                $this->buildContentHeader(),
                $this->buildContent(),
                $this->buildFooter()
            )
        );
        
        // выводим всю страницу.
        echo $html;
    }
}
