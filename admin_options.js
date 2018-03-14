/**
 * Roundcube Plugin Admin Options
 * Extendable plugin to create administrative options
 *
 *
 * @license GNU GPLv3+
 * @author Manuel Delgado
 */
rcube_webmail.prototype.admin_options_select = function(list) {
  if (this.admin_options_timer)
    clearTimeout(rcmail.admin_options_timer);

  var id;
  if (id = list.get_single_selection())
    rcmail.admin_options_timer = window.setTimeout(function(id) { 
      rcmail.admin_options_load(id, 'plugin.admin_options.load', ''); 
    }, 200, id);
}

rcube_webmail.prototype.admin_options_load = function(id, action, add_url) {
  if (action == 'plugin.admin_options.load' && (!id || id == rcmail.env.iid))
    return false;

  var target = window;
  if (rcmail.env.contentframe && window.frames && window.frames[rcmail.env.contentframe]) {
    add_url = add_url + '&_framed=1';
    target = window.frames[rcmail.env.contentframe];
    rcube_find_object(rcmail.env.contentframe).style.visibility = 'inherit';
  }

  if (action && id) {
    rcmail.set_busy(true);
    target.location.href = rcmail.env.comm_path+'&_action='+action+'&_section='+id+add_url;
  }

  return true;
}

$(document).ready(function() {
  if (window.rcmail) {
    rcmail.addEventListener('init', function(evt) {
      if (rcmail.env.action == 'plugin.admin_options') {
        if (rcmail.gui_objects.adminsectionslist) {
          rcmail.admin_options_list = new rcube_list_widget(rcmail.gui_objects.adminsectionslist, {multiselect:false, draggable:false, keyboard:true});

          rcmail.admin_options_list.addEventListener('select', function(o) { rcmail.admin_options_select(o); });
          rcmail.admin_options_list.init();
          rcmail.admin_options_list.focus();

          if (rcmail.env.iid && rcmail.env.iid < rcmail.admin_options_list.rowcount && !rcmail.env.eid)
            rcmail.admin_options_list.select_row(rcmail.env.iid, false, false);
        }
      }
      if (rcmail.env.action == 'plugin.admin_options.load'){
        rcmail.register_command('plugin.admin_options.save', function() {
          rcmail.gui_objects.editform.submit();
        }, true);
      }
    });
  }
});
