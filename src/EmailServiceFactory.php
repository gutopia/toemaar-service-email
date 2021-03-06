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

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;

/**
 * Email Service factory.
 *
 * @package Service_email
 */
class EmailServiceFactory implements FactoryInterface
{
    /**
     * (non-PHPdoc)
     * @see \Zend\ServiceManager\FactoryInterface::createService()
	 * @param ServiceLocatorInterface $serviceLocator
     * @return EmailService
     */
	public function createService(ServiceLocatorInterface $serviceLocator)
	{
		return new EmailService();
	}
}