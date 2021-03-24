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

        self::$extendableMethods[$fullMethodName][] = $callable;
    }

    /**
     *
     */
    public static function methodWasExtended($className, $methodName)
    {
        $fullMethodName = $className.'::'.$methodName;

        if (empty(self::$extendableMethods[$fullMethodName]['callable'])) {
            return false;
        }

        if (empty(self::$extendableMethods[$fullMethodName]['runtime'])) {
            return true;
        }

        return false;
    }

    /**
     *
     */
    public static function callExtendedMethod($self, $className, $methodName, $args)
    {

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
