<?php

$path = $argv[1] ?? 'auditoriaListaOperaciones.var';

class Result
{
    public $grid;
    public $store;
    public $rowActions = [];
    public $form;
    public $formStore;
}
class Parser
{
    const base = '/home/david/projects/openbank/application/fuente/';

    private $path;
    protected $props = [];
    public $result = new Result;

    protected function __construct($path, $props, Result $result)
    {
        $this->path = $path;
        $this->props = $props;
        $this->result = $result;
    }

    public static function fromFile($path, Result $result = new Result): Parser
    {
        $props = eval('return ' . file_get_contents(self::base . $path) . ';');
        $template = $props['_template'];
        $class = preg_replace('/\W/', '', $template);
        return $class($path, $props, $result);
    }

    public function get($key)
    {
        return $this->props[$key];
    }

    public function parse(): Result
    {
        return $this->result;
    }
}

class tpl_H11 extends Parser
{
    public function parse(): Result
    {
        $form = Parser::fromFile($this->get('regionA1'));
        $grid = Parser::fromFile($this->get('regionA2'));
        $this->result->form = $form->parse();
        $this->result->grid = $grid->parse();
        return $this->result;
    }
}

class ide_form
{
    public function parse(): Result
    {
        $fieldset = Parser::fromFile($this->get('regionA1'));
        // $result->formStore = $this->get('store');
        return $fieldset->parse();
    }
}

$result = new Result;
$parser = Parser::fromFile($path, $result);`
echo json_encode($parser->parse(), JSON_PRETTY_PRINT);
