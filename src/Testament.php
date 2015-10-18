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


class Testament
{
    /**
     * @var string
     */
    protected $_fromName = '';

    /**
     * @var string
     */
    protected $_fromAddress = '';

    /**
     * @var string
     */
    protected $_replyTo = '';

    /**
     * @var string
     */
    protected $_subject = '';

    /**
     * @var string
     */
    protected $_parent = '';

    /**
     * @return string
     */
    public function getParent()
    {
        return $this->_parent;
    }

    /**
     * @param string $parent
     *
     * @return Testament
     */
    public function setParent($parent)
    {
        $this->_parent = $parent;

        return $this;
    }

    /**
     * @return string
     */
    public function getFromName()
    {
        return $this->_fromName;
    }

    /**
     * @param string $fromName
     *
     * @return Testament
     */
    public function setFromName($fromName)
    {
        $this->_fromName = $fromName;

        return $this;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->_subject;
    }

    /**
     * @param string $subject
     *
     * @return Testament
     */
    public function setSubject($subject)
    {
        $this->_subject = $subject;

        return $this;
    }

    /**
     * @return string
     */
    public function getFromAddress()
    {
        return $this->_fromAddress;
    }

    /**
     * @param string $fromAddress
     *
     * @return Testament
     */
    public function setFromAddress($fromAddress)
    {
        $this->_fromAddress = $fromAddress;

        return $this;
    }

    /**
     * @return string
     */
    public function getReplyTo()
    {
        return $this->_replyTo;
    }

    /**
     * @param string $replyTo
     *
     * @return Testament
     */
    public function setReplyTo($replyTo)
    {
        $this->_replyTo = $replyTo;

        return $this;
    }

}