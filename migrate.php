<?php

$path = $argv[1] ?? 'auditoriaListaOperaciones';

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
    public $result = null;

    protected function __construct($path, $props, Result $result)
    {
        $this->path = $path;
        $this->props = $props;
        $this->result = $result;
    }

    public static function fromFile($path): Parser
    {
        $result = new Result();
        $props = eval('return ' . file_get_contents(self::base . $path . '.var') . ';');
        $template = $props['_template'];
        $class = preg_replace('/\W/', '', $template);
        return new $class($path, $props, $result);
    }

    public function get($key)
    {
        return $this->props[$key];
    }

    public function parse()
    {
        return $this->result;
    }

    protected function parseStore()
    {
        return [];
    }
}

class tpl_H11 extends Parser
{
    public function parse()
    {
        $form = Parser::fromFile($this->get('regionA1'));
        $grid = Parser::fromFile($this->get('regionA2'));
        return [
            [$form->parse()],
            [$grid->parse()],
        ];
    }
}

class ide_form extends Parser
{
    public function parse()
    {
        $title = $this->get('title');
        $store = $this->parseStore();
        $fieldset = Parser::fromFile($this->get('regionA1'));
        return [
            'component' => 'Form',
            'name' => $this->get('_name'),
            'title' => $title,
            'content' => $fieldset->parse(),
            'store' => $store,
        ];
    }
}

class ide_grilla extends Parser
{
    public function parse()
    {
        $fields = explode("\n", $this->get('fields'));
        $headers = [];
        foreach ($fields as $i => $field) {
            $headers[$i] = [
                'field' => $field,
                'value' => 'attributes.' . $field,
            ];
        }
        $columns = explode("\n", $this->get('columns'));
        foreach ($columns as $i => $column) {
            $column = explode(',', $column);
            $headers[$i] = [
                'label' => $column[0],
                // 'width' => $column[1],
                'align' => $column[2] ?? 'left',
            ];
        }
        return [
            'component' => 'Grid',
            'config' => [
                'headers' => $headers,
            ],
            'store' => $this->parseStore(),
        ];
    }
}

class ide_fieldset extends Parser
{
    const controls = [
        'textfield' => 'TextBox',
        'object' => 'ComboBox',
    ];

    public function parse()
    {
        $fields = explode("\n", $this->get('fields'));
        $result = [];
        foreach ($fields as $i => $field) {
            $field = explode(',', $field);
            $control = self::controls[$field[2] ?? 'textfield'];
            $result[$i] = [[
                'control' => $control,
                'name' => "attributes." . $field[0],
                'label' => $field[1],
                'placeholder' => "",
            ]];
        }
        return $result;
    }
}

$parser = Parser::fromFile($path);
$result = $parser->result;
echo json_encode($parser->parse(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
