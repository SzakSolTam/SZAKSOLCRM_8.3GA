<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

vimport('includes.exceptions.CustomizerException');

class Vtiger_Customizer
{
    /**
     * Define all code customizable resources organized by: methods, functions, inspectors.
     */
    static protected $customizable = array(
        'methods' => array(
            'Vtiger_Viewer::getTemplatePath' => array(
                'file' => 'includes/runtime/Viewer.php',
                'args' => array('templateName', 'moduleName'),
            ),
            'Users::doLogin' => array(
                'file' => 'modules/Users/Users.php',
                'args' => array('password'),
            ),
        ),
        'functions' => array(
            'send_mail' => array(
                'file' => 'modules/Emails/mail.php',
                'args' => array('templateName', 'moduleName'),
            ),
        ),
        'interceptors' => array(
            'crmentity-before-new-instance' => array(
                'file' => 'data/CRMEntity.php',
                'args' => array('module', 'modName'),
            ),
        )
    );

    /**
     *
     * @param $fullMethodName
     * @param $callable
     *
     * @throws CustomizerException
     */
    public static function extendMethod($fullMethodName, $callable)
    {
        if (empty(self::$customizable['methods'][$fullMethodName])) {
            throw new CustomizerException("Method '{$fullMethodName}' cannot be extended");
        }

        self::$customizable['methods'][$fullMethodName]['callable'][] = $callable;
    }

    /**
     * Check if method was extended by custom module.
     *
     * @param $fullMethodName
     *
     * @return bool
     *
     * @throws CustomizerException
     */
    public static function methodWasExtended($fullMethodName)
    {
        if (empty(self::$customizable['methods'][$fullMethodName])) {
            throw new CustomizerException("Method '$fullMethodName' not supported by Customizer.");
        }

        return self::has('methods', $fullMethodName);
    }

    /**
     * @param $self
     * @param $fullMethodName
     * @param $args
     * @return false|mixed
     */
    public static function callExtendedMethod($self, $fullMethodName, $args)
    {
        self::initQueue('methods', $fullMethodName);

        $namedArgs = self::getNamedArgs('methods', $fullMethodName, $args);

        return self::call('methods', $fullMethodName, array($self, $namedArgs));
    }

    /**
     *
     * @param $functionName
     * @param $callable
     *
     * @throws CustomizerException
     */
    public static function extendFunction($functionName, $callable)
    {
        if (empty(self::$customizable[$functionName])) {
            throw new CustomizerException("Function '{$functionName}' cannot be extended");
        }

        self::$customizable['functions'][$functionName]['callable'][] = $callable;
    }

    /**
     * Check if method was extended by custom module.
     *
     * @param $fullMethodName
     *
     * @return bool
     *
     * @throws CustomizerException
     */
    public static function functionWasExtended($functionName)
    {
        if (empty(self::$customizable['functions'][$functionName])) {
            throw new CustomizerException("Function '$functionName' not supported by Customizer.");
        }

        return self::has('functions', $functionName);
    }

    /**
     * @param $self
     * @param $fullMethodName
     * @param $args
     *
     * @return false|mixed
     */
    public static function callExtendedFunction($functionName, $args)
    {
        self::initQueue('functions', $functionName);

        $namedArgs = self::getNamedArgs('methods', $fullMethodName, $args);

        return self::call('functions', $fullMethodName, array($self, $namedArgs));
    }

    /**
     *
     * @param $interceptorTag
     * @param $callable
     *
     * @throws CustomizerException
     */
    public static function addInterceptor($interceptorTag, $callable)
    {
        if (empty(self::$customizable['interceptors'][$interceptorTag])) {
            throw new CustomizerException("Interceptor '{$interceptorTag}' not exists");
        }

        self::$customizable['interceptors'][$interceptorTag]['callable'][] = $callable;
    }

    /**
     * Check if method was extended by custom module.
     *
     * @param $interceptorTag
     * @return bool
     *
     * @throws CustomizerException
     */
    public static function hasInterceptor($interceptorTag)
    {
        if (empty(self::$customizable['interceptors'][$interceptorTag])) {
            throw new CustomizerException("Interceptor '$interceptorTag' not supported by Customizer.");
        }

        return self::has('interceptors', $interceptorTag);
    }

    /**
     * @param $interceptorTag
     * @param $args
     *
     * @return false|mixed
     */
    public static function callInterceptor($interceptorTag, $args)
    {
        self::initQueue('interceptors', $interceptorTag);

        $namedArgs = self::getNamedArgs('interceptors', $interceptorTag, $args);

        return self::call('interceptors', $interceptorTag, array($args));
    }

    /**
     * @param $resource
     * @param $identifier
     *
     * @return bool
     */
    protected static function has($resource, $identifier)
    {
        if (empty(self::$customizable[$resource][$identifier]['callable'])) {
            return false;
        }

        if (empty(self::$customizable[$resource][$identifier]['runtime'])) {
            return true;
        } elseif (count(self::$customizable[$resource][$identifier]['queue']) > 0) {
            return true;
        }

        return false;
    }

    /**
     * @param $resource
     * @param $identifier
     */
    protected static function initQueue($resource, $identifier)
    {
        if (empty(self::$customizable[$resource][$identifier]['queue'])) {
            self::$customizable[$resource][$identifier]['queue']
                = self::$customizable[$resource][$identifier]['callable'];
        }
    }

    /**
     *
     */
    protected static function call($resource, $identifier, $callableArgs)
    {
        $return = self::callNext($resource, $identifier, $callableArgs);

        while (self::hasNext($resource, $identifier)) {
            $return = self::callNext($resource, $identifier, $callableArgs);
        }

        return $return;
    }

    /**
     * @param $resource
     * @param $identifier
     * @param $callableArgs
     *
     * @return false|mixed
     */
    protected static function callNext($resource, $identifier, $callableArgs)
    {
        $callable = array_pop(self::$customizable[$resource][$identifier]['queue']);
        self::$customizable[$resource][$identifier]['runtime'] = true;
        $return = call_user_func_array($callable, $callableArgs);
        unset(self::$customizable[$resource][$identifier]['runtime']);

        return $return;
    }

    /**
     * @param $resource
     * @param $identifier
     *
     * @return bool
     */
    protected static function hasNext($resource, $identifier)
    {
        return count(self::$customizable[$resource][$identifier]['queue']) > 0;
    }

    /**
     * @param $resource
     * @param $identifier
     * @param $args
     *
     * @return array
     */
    protected static function getNamedArgs($resource, $identifier, $args)
    {
        return array_combine(self::$customizable[$resource][$identifier]['args'], $args);
    }
}
