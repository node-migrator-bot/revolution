<?php
/**
 * MODX Revolution
 *
 * Copyright 2006-2011 by MODX, LLC.
 * All rights reserved.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, write to the Free Software Foundation, Inc., 59 Temple
 * Place, Suite 330, Boston, MA 02111-1307 USA
 *
 * @package modx-test
 */
/**
 * Tests related to browser/directory/ processors
 *
 * @package modx-test
 * @subpackage modx
 * @group Processors
 * @group BrowserProcessors
 */
class BrowserDirectoryProcessorsTest extends MODxTestCase {
    const PROCESSOR_LOCATION = 'browser/directory/';

    public static function setUpBeforeClass() {
        $modx = MODxTestHarness::_getConnection();
        $modx->setOption('filemanager_path','');
        $modx->setOption('filemanager_url','');
        $modx->setOption('rb_base_dir','');
        $modx->setOption('rb_base_url','');
        @rmdir(MODX_BASE_PATH.'assets/test2/');
        @rmdir(MODX_BASE_PATH.'assets/test3/');
        @rmdir(MODX_BASE_PATH.'assets/test4/');
    }
    /**
     * Cleanup data after this test case.
     */
    public static function tearDownAfterClass() {
        @rmdir(MODX_BASE_PATH.'assets/test2/');
        @rmdir(MODX_BASE_PATH.'assets/test3/');
        @rmdir(MODX_BASE_PATH.'assets/test4/');
    }
    /**
     * Tests the browser/directory/create processor, which creates a directory
     * @dataProvider providerCreateDirectory
     */
    public function testCreateDirectory($dir = '') {
        if (empty($dir)) {
            $this->fail('Empty data set!');
            return false;
        }
        $this->modx->setOption('filemanager_path','');
        $this->modx->setOption('filemanager_url','');
        $this->modx->setOption('rb_base_dir','');
        $this->modx->setOption('rb_base_url','');

        $result = $this->modx->runProcessor(self::PROCESSOR_LOCATION.'create',array(
            'name' => $dir,
        ));
        if (empty($result)) {
            $this->fail('Could not load '.self::PROCESSOR_LOCATION.'create processor');
        }
        $s = $this->checkForSuccess($result);
        $this->assertTrue($s,'Could not create directory '.$dir.' in browser/directory/create test: '.$result->getMessage());
    }
    /**
     * Data provider for create processor test.
     */
    public function providerCreateDirectory() {
        return array(
            array('assets/test2'),
            array('assets/test3'),
        );
    }

    /**
     * Tests the browser/directory/update processor, which renames a directory
     * 
     * @depends testCreateDirectory
     * @dataProvider providerUpdateDirectory
     */
    public function testUpdateDirectory($oldDirectory = '',$newDirectory = '') {
        if (empty($oldDirectory) || empty($newDirectory)) return false;
        $this->modx->setOption('filemanager_path','');
        $this->modx->setOption('filemanager_url','');
        $this->modx->setOption('rb_base_dir','');
        $this->modx->setOption('rb_base_url','');

        $adir = $this->modx->getOption('base_path').$oldDirectory;
        @mkdir($adir);
        if (!file_exists($adir)) {
            $this->fail('Old directory could not be created for test: '.$adir);
        }

        $result = $this->modx->runProcessor(self::PROCESSOR_LOCATION.'update',array(
            'dir' => $oldDirectory,
            'name' => $this->modx->getOption('base_path').$newDirectory,
        ));
        if (empty($result)) {
            $this->fail('Could not load '.self::PROCESSOR_LOCATION.'update processor');
        }
        $s = $this->checkForSuccess($result);
        $this->assertTrue($s,'Could not rename directory '.$oldDirectory.' to '.$newDirectory.' in browser/directory/update test: '.$result->getMessage());
    }
    /**
     * Data provider for update processor test
     */
    public function providerUpdateDirectory() {
        return array(
            array('assets/test3/','assets/test4'),
        );
    }

    /**
     * Tests the browser/directory/remove processor, which removes a directory
     * @dataProvider providerRemoveDirectory
     * @depends testCreateDirectory
     * @depends testUpdateDirectory
     * @param string $dir
     */
    public function testRemoveDirectory($dir = '') {
        if (empty($dir)) return false;
        $this->modx->setOption('filemanager_path','');
        $this->modx->setOption('filemanager_url','');
        $this->modx->setOption('rb_base_dir','');
        $this->modx->setOption('rb_base_url','');

        $adir = $this->modx->getOption('base_path').$dir;
        @mkdir($adir);
        if (!file_exists($adir)) {
            $this->fail('Old directory could not be created for test: '.$adir);
        }

        $result = $this->modx->runProcessor(self::PROCESSOR_LOCATION.'remove',array(
            'dir' => $dir,
        ));
        if (empty($result)) {
            $this->fail('Could not load '.self::PROCESSOR_LOCATION.'remove processor');
        }
        $s = $this->checkForSuccess($result);
        $this->assertTrue($s,'Could not remove directory: `'.$dir.'`: '.$result->getMessage());
    }
    /**
     * Data provider for remove processor test.
     */
    public function providerRemoveDirectory() {
        return array(
            array('assets/test2'),
            array('assets/test4'),
        );
    }

    /**
     * Tests the browser/directory/getList processor
     * 
     * @dataProvider providerGetDirectoryList
     * @param string $dir A string path to the directory to list.
     * @param boolean $shouldWork True if the directory list should not be empty.
     * @param string $filemanager_path A custom filemanager_path
     * @param string $filemanager_url A custom filemanager_url
     */
    public function testGetDirectoryList($dir,$shouldWork = true,$filemanager_path = '',$filemanager_url = '') {
        $this->modx->setOption('filemanager_path',$filemanager_path);
        $this->modx->setOption('filemanager_url',$filemanager_url);
        $this->modx->setOption('rb_base_dir','');
        $this->modx->setOption('rb_base_url','');

        $result = $this->modx->runProcessor(self::PROCESSOR_LOCATION.'getList',array(
            'id' => $dir,
        ));
        if (empty($result)) {
            $this->fail('Could not load '.self::PROCESSOR_LOCATION.'getList processor');
        }
        if (!is_array($result)) $result = $result->getResponse();

        /* ensure correct test result */
        $success = $shouldWork ?
            empty($result['success']) || $result['success'] == true
            : isset($result['success']) && $result['success'] == false;
        $this->assertTrue($success,'Could get list of files and dirs for '.$dir.' in browser/directory/getList test: '.$result['message']);
    }
    /**
     * Test data provider for getList processor
     */
    public function providerGetDirectoryList() {
        $modx = MODxTestHarness::_getConnection();
        return array(
            array('manager/',true),
            array('manager/assets',true),
            array('fakedirectory/',false),
            array('assets',true,$modx->getOption('manager_path'),$modx->getOption('manager_url')),
        );
    }
}