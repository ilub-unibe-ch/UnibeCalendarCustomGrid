<?php

namespace iLub\Plugin\UnibeCalendarCustomGrid\Ctrl;

/**
 * Class CtrlHandler
 *
 * @author Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 */
trait CtrlHandler {

	/**
	 * @param ICtrlAware $ctrlAware
	 * @return bool whether a next class has handled the request or not
	 * @throws \ilCtrlException
	 */
	public function handleNextClass(ICtrlAware $ctrlAware) {
		if (!$this instanceof ICtrlAware) {
			throw new \LogicException("Can't use trait CtrlHandler in classes which do not implement ICtrlAware");
		}
		/**
		 * @var $this ICtrlAware
		 */
		$next_class = $ctrlAware->ctrl()->getNextClass();
		foreach ($ctrlAware->getPossibleNextClasses() as $class) {
			if (strtolower($class) === $next_class) {
				/**
				 * @var $instance ICtrlAware
				 */
				$instance = new $class($ctrlAware->ctrl(), $ctrlAware->tpl(), $ctrlAware->language(), $ctrlAware->tabs(), $ctrlAware->user(), $ctrlAware->access());
				if ($instance instanceof ICtrlAware) {
					$instance->setParentController($this);
					$ctrlAware->ctrl()->forwardCommand($instance);
				}

				return true;
			}
		}

		return false;
	}
}
