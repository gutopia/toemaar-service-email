<?php
/**
 * Email package that provides a easy email templateing service.
 * The email is constructed and sent of through the configured
 * transport protocol.
 *
 * Toemaar (http://code.grasvezel.nl/)
 *
 * @link      http://code.grasvezel.nl/
 * @package   Toemaar/Service/Email
 * @copyright Copyright (c) 2005-2015 Toemaar. (http://www.toemaar.nl)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Toemaar\Email;

class Template
{

    /**
     * @var string
     */
    protected $_i18n = null;

    /**
     * @var string
     */
    protected $_template = 'default';

    /**
     * @var null|string
     */
    protected $_htmlTemplate = null;

    /**
     * @var null|string
     */
    protected $_textTemplate = null;

    /**
     * @var string[]
     */
    protected $_variables = array();

    /**
     * @var string
     */
    protected $_templatesRoot = '';

    /**
     * @var null|Testament
     */
    protected $_testament = null;

    /**
     * @var string
     */
    protected $_renderedHtml = '';

    /**
     * @var string
     */
    protected $_renderedText = '';
    
    /**
     * 
     * @var Template
     */
    protected $_parent = null;
    
    /**
     *
     * @var string
     */
    protected $_parentTemplate = '';
    
    public function __construct(){

    }

    /**
     * Set email i18n
     *
     * @param string $i18n
     * @return Template
     */
    public function setI18n($i18n){
        $this->_i18n = $i18n;

        return $this;
    }

    /**
     * Set template name
     *
     * @param string $template
     *
     * @return Template
     * @throws Exception
     */
    public function setTemplate($template){
        if(!is_dir($this->_templatesRoot . '/' . $template))
            throw new Exception('Email Template ' . $template . ' could not be found. (in: ' . $this->_templatesRoot . ')');

        $this->_template = $template;
        return $this;
    }

    /**
     * @param null $templateRoot
     *
     * @return Template
     */
    public function setTemplateRoot($templateRoot = null){
        if(null === $templateRoot)
            $templateRoot = __DIR__ . '/templates/';

        $this->_templatesRoot = $templateRoot;

        return $this;
    }
    
    /**
     * Property overloading: get variable value
     *
     * @param  string $name
     * @return mixed
     */
    public function __get($name)
    {
        if (!isset($this->_variables[$name])) {
            return null;
        }
    
        return $this->_variables[$name];
    }
    
    /**
     * Get a single view variable
     *
     * @param  string       $name
     * @param  mixed|null   $default (optional) default value if the variable is not present.
     * @return mixed
     */
    public function getVariable($name, $default = null)
    {
        $name = (string) $name;
        if (array_key_exists($name, $this->_variables)) {
            return $this->_variables[$name];
        }
    
        return $default;
    }

    /**
     * Set view variable
     *
     * @param  string $name
     * @param  mixed $value
     * @return Template
     */
    public function setVariable($name, $value)
    {
        $this->_variables[(string) $name] = $value;
        
        if(null !== $this->_parent)
            $this->_parent->setVariables($this->_variables);
        return $this;
    }

    /**
     * Set view variables en masse
     *
     *
     * @param  array $variables
     * @return Template
     */
    public function setVariables($variables)
    {
        $this->_variables = $variables;
        if(null !== $this->_parent)
            $this->_parent->setVariables($this->_variables);
        
        return $this;
    }

    protected function findFiles(){
        $path = $this->_templatesRoot . '/' .$this->_template;

        if(!is_dir($path))
            throw new Exception('Template ' . $this->_template . ' is not found.');

        if(!is_file($path . '/' . $this->_i18n . '.json'))
            throw new Exception('Template testament file is not found. (' . $path . '/' . $this->_i18n . '.json)');

        $testamentObject = json_decode(file_get_contents($path . '/' . $this->_i18n . '.json'));

        if(null == $testamentObject)
            throw new Exception('Invalid Json in testament');

        $this->_testament = new Testament();
        $this->_testament
            ->setFromAddress($testamentObject->Replyto)
            ->setFromName($testamentObject->From->Name)
            ->setSubject($testamentObject->Subject)
            ->setReplyTo($testamentObject->From->Address)
            ->setParent($testamentObject->ParentTheme);

        if($this->_testament->getParent())
            $this->setParent($this->_testament->getParent());
        
        if(!is_file($path . '/' . $this->_i18n . '.phtml'))
            throw new Exception('Html file for template could not be found. (' . $path . '/' . $this->_i18n . '.phtml)');
        
        if(!is_file($path . '/' . $this->_i18n . '.ptext'))
            throw new Exception('Text file for template could not be found. (' . $path . '/' . $this->_i18n . '.ptext)');
                    
        $this->_htmlTemplate = $path . '/' . $this->_i18n . '.phtml';
        $this->_textTemplate = $path . '/' . $this->_i18n . '.ptext';
    }

    public function setParent($parentTheme){
        $path = (substr($this->_templatesRoot, 0, -1) == '/' ? substr($this->_templatesRoot, 0, strlen($this->_templatesRoot)-1) : $this->_templatesRoot);

        $this->_parent = new Template();
        $this->_parent
            ->setTemplateRoot($path)
            ->setI18n($this->_i18n)
            ->setTemplate($parentTheme);
    }

    /**
     * @return Testament
     */
    public function getTestament(){
        return $this->_testament;
    }

    /**
     * @param $templateFile
     *
     * @return string
     */
    protected function readTemplateFile($templateFile){
        ob_start();
        include($templateFile);
        return ob_get_clean();
        
    }

    /**
     * Get html content of email
     * @return string
     */
    public function getHtml()
    {
        return $this->_renderedHtml;
    }


    /**
     * Get Text content of email
     *
     * @return string
     */
    public function getText()
    {
        return $this->_renderedText;
    }

    public function renderForChild($html, $text){
        $this->findFiles();
        $this->_variables['childcontent'] = $html;
        $this->_renderedHtml = $this->readTemplateFile($this->_htmlTemplate);
        $this->_variables['childcontent'] = $text;
        $this->_renderedText = $this->readTemplateFile($this->_textTemplate);
        if($this->_parent){
            $this->_parent->setVariables($this->_variables);
            $this->_parent->renderForChild($this->_renderedHtml, $this->_renderedText);
            $this->_renderedHtml = $this->_parent->getHtml();
            $this->_renderedText = $this->_parent->getText();
        }

        return $this;
    }

    /**
     * Render html and text content for email
     *
     * @return $this
     * @throws Exception
     */
    public function render()
    {
        $this->findFiles();
        $this->_renderedHtml = $this->readTemplateFile($this->_htmlTemplate); 
        $this->_renderedText = $this->readTemplateFile($this->_textTemplate);

        if($this->_parent){
            $this->_parent->setVariables($this->_variables);
            $this->_parent->renderForChild($this->_renderedHtml, $this->_renderedText);
            $this->_renderedHtml = $this->_parent->getHtml();
            $this->_renderedText = $this->_parent->getText();
        }

        return $this;
    }
    
}