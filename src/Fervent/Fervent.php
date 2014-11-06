<?php namespace Fervent;

/*
 * This file is part of the Fervent package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Closure;
use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager as DatabaseCapsule;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Validation\DatabasePresenceVerifier;
use Illuminate\Validation\Factory as ValidationFactory;
use Symfony\Component\Translation\Loader\PhpFileLoader;
use Symfony\Component\Translation\Translator;

/**
 * FerventTraits - Self-validating traits intended for Eloquent model base class
 *
 */
abstract class Fervent extends Model {
    use FerventTrait;
}
