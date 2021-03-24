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
     *
     */
    static protected $extendableMethods = array(
        'Vtiger_Viewer::getTemplatePath' => array(
            'file' => 'includes/runtime/Viewer.php',
            'args' => array('templateName', 'moduleName'),
        ),
        'Users::doLogin' => array(
            'file' => 'modules/Users/Users.php',
            'args' => array('password'),
        ),
    );

    /**
     *
     */
    static protected $extendableFunctions = array(
        'send_mail' => array(
            'file' => 'modules/Emails/mail.php',
            'args' => array('templateName', 'moduleName'),
        ),
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
        if (empty(self::$extendableMethods[$fullMethodName])) {
            throw new CustomizerException("Method '{$fullMethodName}' cannot be extended");
        }

        self::$extendableMethods[$fullMethodName]['callable'][] = $callable;
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
        if (empty(self::$extendableMethods[$fullMethodName])) {
            throw new CustomizerException("Method '$fullMethodName' not supported by Customizer.");
        }

        if (empty(self::$extendableMethods[$fullMethodName]['callable'])) {
            return false;
        }

        if (empty(self::$extendableMethods[$fullMethodName]['runtime'])) {
            return true;
        } elseif (count(self::$extendableMethods[$fullMethodName]['queue']) > 0) {
            return true;
        }

        return false;
    }

    /**
     * @param $self
     * @param $fullMethodName
     * @param $args
     * @return false|mixed
     */
    public static function callExtendedMethod($self, $fullMethodName, $args)
    {
        if (empty(self::$extendableMethods[$fullMethodName]['queue'])) {
            self::$extendableMethods[$fullMethodName]['queue'] = self::$extendableMethods[$fullMethodName]['callable'];
        }

        $namedArgs = array_combine(self::$extendableMethods[$fullMethodName]['args'], $args);

        $return = self::callNextExtendedMethod($self, $namedArgs);

        while (count(self::$extendableMethods[$fullMethodName]['queue']) > 0) {
            $return = self::callNextExtendedMethod($self, $namedArgs);
        }

        return $return;
    }

    /**
     * @param $self
     * @param $fullMethodName
     * @param $namedArgs
     * @return false|mixed
     */
    protected static function callNextExtendedMethod($self, $fullMethodName, $namedArgs)
    {
        $callable = array_pop(self::$extendableMethods[$fullMethodName]['queue']);
        self::$extendableMethods[$fullMethodName]['runtime'] = true;
        $return = call_user_func_array($callable, array($self, $namedArgs));
        unset(self::$extendableMethods[$fullMethodName]['runtime']);

        return $return;
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
        if (empty(self::$extendableFunctions[$functionName])) {
            throw new CustomizerException("Function '{$functionName}' cannot be extended");
        }
    }
}
