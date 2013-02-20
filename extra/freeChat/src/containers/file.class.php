<?php
/**
 * file.class.php
 *
 * Copyright � 2006 Stephane Gully <stephane.gully@gmail.com>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful, 
 * but WITHOUT ANY WARRANTY; without even the implied warranty of 
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details. 
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the
 * Free Software Foundation, 51 Franklin St, Fifth Floor,
 * Boston, MA  02110-1301  USA
 */

require_once dirname(__FILE__)."/../pfccontainerinterface.class.php";

/**
 * pfcContainer_File is a concret container which stock data into files
 *
 * @author Stephane Gully <stephane.gully@gmail.com>
 */
class pfcContainer_File extends pfcContainerInterface
{
  var $_users = array("nickid"    => array(),
                      "timestamp" => array());
  var $_meta = array();
  
  function pfcContainer_File()
  {
    pfcContainerInterface::pfcContainerInterface();
  }
  
  function loadPaths(&$c)
  {
    if (!isset($c->container_cfg_chat_dir) || $c->container_cfg_chat_dir == '')
      $c->container_cfg_chat_dir   = $c->data_private_path."/chat";
    if (!isset($c->container_cfg_server_dir) || $c->container_cfg_server_dir == '')
      $c->container_cfg_server_dir = $c->container_cfg_chat_dir."/s_".$c->serverid;
  }
  
  function getDefaultConfig()
  {
    $cfg = pfcContainerInterface::getDefaultConfig();
    $cfg["chat_dir"]   = ''; // will be generated from the other parameters into the init step
    $cfg["server_dir"] = ''; // will be generated from the other parameters into the init step
    return $cfg;
  }
  
  function init(&$c)
  {
    $errors = pfcContainerInterface::init($c);

    // generate the container parameters from other config parameters
    $this->loadPaths($c);
   
    $errors = array_merge($errors, @test_writable_dir($c->container_cfg_chat_dir,   "container_cfg_chat_dir"));
    $errors = array_merge($errors, @test_writable_dir($c->container_cfg_server_dir, "container_cfg_chat_dir/serverid"));

    return $errors;
  }


  function setMeta($group, $subgroup, $leaf, $leafvalue = NULL)
  {
    $c =& pfcGlobalConfig::Instance();

    if ($c->debug)
      file_put_contents("/tmp/debug", "\nsetMeta(".$group.",".$subgroup.",".$leaf.",".$leafvalue.")", FILE_APPEND);
    
    // create directories
    $dir_base = $c->container_cfg_server_dir;
    $dir = $dir_base.'/'.$group.'/'.$subgroup;
    if (!is_dir($dir)) mkdir_r($dir);
    
    // create or replace metadata file
    $leaffilename = $dir."/".$leaf;
    $leafexists = file_exists($leaffilename);
    if ($leafvalue == NULL)
    {
      if (file_exists($leaffilename) &&
          filesize($leaffilename)>0) unlink($leaffilename);
      touch($leaffilename);     
    }
    else
    {
      file_put_contents($leaffilename, $leafvalue);
    }

    // store the value in the memory cache
    //@todo
    //    $this->_meta[$enc_type][$enc_subtype][$enc_key] = $value;

    if ($leafexists)
      return 1; // value overwritten
    else
      return 0; // value created
  }

  
  function getMeta($group, $subgroup = null, $leaf = null, $withleafvalue = false)
  {
    $c =& pfcGlobalConfig::Instance();
    if ($c->debug)
      file_put_contents("/tmp/debug", "\ngetMeta(".$group.",".$subgroup.",".$leaf.",".$withleafvalue.")", FILE_APPEND);
    
    // read data from metadata file
    $ret = array();
    $ret["timestamp"] = array();
    $ret["value"]     = array();
    $dir_base = $c->container_cfg_server_dir;

    $dir = $dir_base.'/'.$group;
    if ($subgroup == NULL)
    {
      if (is_dir($dir))
      {
        $dh = opendir($dir);
        while (false !== ($file = readdir($dh)))
        {
          if ($file == "." || $file == "..") continue; // skip . and .. generic files
          $ret["timestamp"][] = filemtime($dir.'/'.$file);
          $ret["value"][]     = $file;
        }
        closedir($dh);
      }
      return $ret;
    }
    
    $dir .= '/'.$subgroup;

    if ($leaf == NULL)
    {
      if (is_dir($dir))
      {
        $dh = opendir($dir);
        while (false !== ($file = readdir($dh)))
        {
          if ($file == "." || $file == "..") continue; // skip . and .. generic files
          $ret["timestamp"][] = filemtime($dir.'/'.$file);
          $ret["value"][]     = $file;
        }
        closedir($dh);
      }

      return $ret;
    }
    
    $leaffilename = $dir."/".$leaf;

    if (!file_exists($leaffilename)) return $ret;
    if ($withleafvalue)
      $ret["value"][] = file_get_contents($leaffilename);
    else
      $ret["value"][] = NULL;
    $ret["timestamp"][] = filemtime($leaffilename);

    return $ret;
  }

  function rmMeta($group, $subgroup = null, $leaf = null)
  {
    $c =& pfcGlobalConfig::Instance();
    if ($c->debug)
      file_put_contents("/tmp/debug", "\nrmMeta(".$group.",".$subgroup.",".$leaf.")", FILE_APPEND);
    
    $dir = $c->container_cfg_server_dir;

    if ($group == NULL)
    {
      rm_r($dir);
      return true;
    }

    $dir .= '/'.$group;

    if ($subgroup == NULL)
    {
      rm_r($dir);
      return true;
    }
    
    $dir .= '/'.$subgroup;

    if ($leaf == NULL)
    {
      rm_r($dir);
      return true;
    }
    
    $leaffilename = $dir.'/'.$leaf;    
    if (!file_exists($leaffilename)) return false;
    unlink($leaffilename);
    
    // check that the directory is empty or not
    // remove it if it doesn't contains anything
    $dh = opendir($dir);
    readdir($dh); readdir($dh); // skip . and .. directories
    $isnotempty = readdir($dh);
    closedir($dh);
    if ($isnotempty === false) rmdir($dir);
    
    return true;
  }


  /**
   * Used to encode UTF8 strings to ASCII filenames
   */  
  function encode($str)
  {
    return urlencode($str);
  }
  
  /**
   * Used to decode ASCII filenames to UTF8 strings
   */  
  function decode($str)
  {
    return urldecode($str);
  }

}

?>