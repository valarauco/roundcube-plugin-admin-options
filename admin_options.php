<?php

/**
 * Roundcube Plugin Admin Options
 * Extendable plugin to create administrative options
 *
 *
 * @license GNU GPLv3+
 * @author Manuel Delgado
 */
class admin_options extends rcube_plugin
{
    private $rc;
    private $plug_name = 'plugin.admin_options';
    public $task    = 'settings';

    function init()
    {
        $this->rc = rcmail::get_instance();
        $this->load_config();       
        // Settings Tab
        if ($this->rc->task == 'settings') { 
          $user = $this->rc->user->get_username();
          $admins =  $this->rc->config->get('admin_options_users', array());
          if (in_array($user, $admins, true)) {
            $this->add_texts('localization');
            $this->add_hook('settings_actions', array($this, 'settings_tab'));
            $this->register_action($this->plug_name, array($this, 'init_html'));
            $this->register_action($this->plug_name.'.load', array($this, 'load_html'));
            $this->register_action($this->plug_name.'.save', array($this, 'save_html'));
          }
        }
    }
    
    // ADMIN OPTION UI //   
    function settings_tab($p)
	  {
	    $p['actions'][] = array('action' => $this->plug_name, 
	                        'type' => 'link', 
	                        'class' => 'admin-options', 
	                        'label' => 'admin_options.administration', 
	                        'title' => 'admin_options.administration');

      return $p;
	  }
	  
	  function admin_frame($attrib)
	  {
		  if (!$attrib['id'])
			  $attrib['id'] = 'rcmprefsframe';

		  return $this->api->output->frame($attrib, true);
	  }
	  
	  /*
	   * Generate List of available Admin Options and it's data
	   * Triggers: 
	   *    admin_options_list hook
 	   *    admin_options_data hook
	   *    admin_options_header hook
	   */
	  function admin_options_load($current = null)
    {
		  // list_options['list'][option_name]['id']['section'];
      $list_options = $this->rc->plugins->exec_hook('admin_options_list',
          array('list' => array(), 'cols' => array('section')));
      
      $sections = $list_options['list'];
      foreach ($sections as $idx => $sect) {
        $sections[$idx]['class'] = $idx;
        
        if ($current and $sect['id'] != $current) {
            continue;
        }
        $blocks = array();
        
        $found = false;
        $data  = $this->rc->plugins->exec_hook('admin_options_data',
            array('section' => $sect['id'], 'blocks' => $blocks, 'current' => $current));
        
        foreach ($data['blocks'] as $key => $block) {
          if (!empty($block['content']) || !empty($block['options'])) {
            $found = true;
          }
        }
        
        if (!$found) {
          unset($sections[$idx]);
        } else {
          $sections[$idx]['blocks'] = $data['blocks'];
        }
        $data = $this->rc->plugins->exec_hook('admin_options_header',
            array('section' => $sect['id'], 'header' => '', 'current' => $current));

        if (!empty($data['header'])) {
          $sections[$idx]['header'] = $data['header'];
        }
      }

      return array($sections, $list_options['cols']);
    }
    
    function admin_options_list($attrib)
    {
      // add id to message list table if not specified
      if (!strlen($attrib['id'])) {
          $attrib['id'] = 'sections-table';
      }

      list($list, $cols) = $this->admin_options_load();

      // create XHTML table
      $out = $this->rc->table_output($attrib, $list, $cols, 'id');

      $this->rc->output->add_gui_object('adminsectionslist', $attrib['id']);
      $this->rc->output->include_script('list.js');

      return $out;
    }
    
    function admin_options_form($attrib)
    {
        $current    = rcube_utils::get_input_value('_section', rcube_utils::INPUT_GPC);
        list($sections,) = $this->admin_options_load($current);

        unset($attrib['form']);

        list($form_start, $form_end) = get_form_tags($attrib, 'plugin.admin_options.save', null,
            array('name' => '_section', 'value' => $current));

        $out = $form_start;

        if(!empty($sections[$current]['header'])) {
            $out .= html::div(array('id' => 'preferences-header', 'class' =>'boxcontent'), $sections[$current]['header']);
        }

        foreach ($sections[$current]['blocks'] as $class => $block) {
            if (!empty($block['options'])) {
                $table = new html_table(array('cols' => 2));

                foreach ($block['options'] as $option) {
                    if (isset($option['title'])) {
                        $table->add('title', $option['title']);
                        $table->add(null, $option['content']);
                    }
                    else {
                        $table->add(array('colspan' => 2), $option['content']);
                    }
                }

                $out .= html::tag('fieldset', $class, html::tag('legend', null, $block['name']) . $table->show($attrib));
            }
            else if (!empty($block['content'])) {
                $out .= html::tag('fieldset', null, html::tag('legend', null, $block['name']) . $block['content']);
            }
        }

        return $out . $form_end;
    }
    
    function admin_options_section_name()
    {
        $current    = rcube_utils::get_input_value('_section', rcube_utils::INPUT_GPC);
        list($sections,) = $this->admin_options_load($current);

        return $sections[$current]['section'];
    }
	  
	  /*
	   * Init the option list (sections column)
	   */
	  function init_html()
	  {
		  $this->include_script('admin_options.js');
		  
	    $this->api->output->add_handlers(array(
			  'adminsectionslist' => array($this, 'admin_options_list'),
			  'prefsframe' => array($this, 'admin_frame'),
		  ));
      
      $this->api->output->set_pagetitle($this->gettext('administration'));
		  $this->api->output->send('admin_options.admin_options');
	  }
	  
	  /*
	   * Init the option data (main form column)
	   */
	  function load_html ($attrib = null)
	  {
		  $this->include_script('admin_options.js');
		  
		  $this->rc->html_editor('adminoptions');
			$this->api->output->add_script(sprintf("window.rcmail_editor_settings = %s",
				json_encode(array(
				'plugins' => 'autolink charmap code colorpicker hr link paste tabfocus textcolor',
				'toolbar' => 'bold italic underline alignleft aligncenter alignright alignjustify | outdent indent charmap hr | link unlink | code forecolor | fontselect fontsizeselect'
			))), 'head');
		  		  
	    $this->api->output->add_handlers(array(
          'userprefs'   => array($this, 'admin_options_form'),
          'sectionname' => array($this, 'admin_options_section_name'),
      ));
      
		  $this->api->output->send('admin_options.options_load');
	  }
	  
	  /*
	   * Process the save action
	   */
	  function save_html ($attrib = null)
	  {
	    $current    = rcube_utils::get_input_value('_section', rcube_utils::INPUT_GPC);
      $data  = $this->rc->plugins->exec_hook('admin_options_save',
            array('section' => $current, 'abort' => false));
	    
      $this->rc->overwrite_action('plugin.admin_options.load');
		  $this->load_html($attrib);
	  } 
}

