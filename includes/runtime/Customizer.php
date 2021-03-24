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
        'Users::doLogin' => array('file' => 'modules/Users/Users.php'),
    );

    /**
     *
     */
    static protected $extendableFunctions = array(
        'send_mail' => 'modules/Emails/mail.php',
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
     *
     */
    public static function methodWasExtended($fullMethodName)
    {
        if (empty(self::$extendableMethods[$fullMethodName])) {
            throw new CustomizerException("Method $fullMethodName not registerd for custmizer");
        }

        if (empty(self::$extendableMethods[$fullMethodName]['callable'])) {
            return false;
        }

        if (empty(self::$extendableMethods[$fullMethodName]['runtime'])) {
            self::$extendableMethods[$fullMethodName]['runtime'] = self::$extendableMethods[$fullMethodName]['callable'];
            return true;
        }

        if (count(self::$extendableMethods[$fullMethodName]['runtime']) < count(self::$extendableMethods[$fullMethodName]['callable'])) {
            return true;
        }

        return false;
    }

    /**
     *
     */
    public static function callExtendedMethod($self, $fullMethodName, $args)
    {
        $callable = array_pop(self::$extendableMethods[$fullMethodName]['runtime']);

        $namedArgs = array_combine(self::$extendableMethods[$fullMethodName]['args'], $args);

        return call_user_func_array($callable, array($self, $namedArgs));
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
