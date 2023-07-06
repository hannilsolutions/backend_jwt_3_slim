<?php
namespace App\DomainDTO;

  class TreeNode
{

    protected $label;

    protected $type;

    protected $styleClass;

    protected $expanded;

    protected $data;

    protected $children;

    

    public function get_label()
    {
        return $this->label;
    }

    public function set_label($label)
    {
        $this->label = $label;
    }

    public function get_type()
    {
        return $this->type;
    }

    public function set_type($type)
    {
        $this->type = $type;
    }

    public function get_styleClass()
    {
        return $this->styleClass;
    }

    public function set_styleClass($styleClass)
    {
        $this->styleClass = $styleClass;
    }

    public function get_expanded()
    {
        return $this->expanded;
    }

    public function set_expanded($expanded)
    {
        $this->expanded = $expanded;
    }
    
    public function get_data()
    {
        return $this->data;
    }

    public function set_data($data)
    {
        $this->data = $data;
    }

    public function get_children()
    {
        return $this->children;
    }

    public function set_children($children)
    {
        $this->children = $children;
    }
}


?>