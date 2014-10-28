<?php namespace Fervent;

/**
 * Used when validation fails. Contains the invalid model for easy analysis.
 * Class InvalidModelException
 * @package Fervent
 */
class InvalidModelException extends \RuntimeException {

	/**
	 * The invalid model.
	 * @var \LaravelBook\Fervent\Fervent
	 */
	protected $model;

	/**
	 * The message bag instance containing validation error messages
	 * @var \Illuminate\Support\MessageBag
	 */
	protected $errors;

	/**
	 * Receives the invalid model and sets the {@link model} and {@link errors} properties.
	 * @param Fervent $model The troublesome model.
	 */
	public function __construct(Fervent $model) {
		$this->model  = $model;
		$this->errors = $model->errors();
	}

	/**
	 * Returns the model with invalid attributes.
	 * @return Fervent
	 */
	public function getModel() {
		return $this->model;
	}

	/**
	 * Returns directly the message bag instance with the model's errors.
	 * @return \Illuminate\Support\MessageBag
	 */
	public function getErrors() {
		return $this->errors;
	}
}
