<?php
/*
SAML Authentication Plugin Custom Hook


This file acts as a hook for the SAML Authentication plugin. The plugin will
call the functions defined in this file in certain points in the plugin
lifecycle.

Use this sample file as a template. You should copy it and not modify it
in place since you may lost your changes in future updates.

To use this hook you have to go to the config form in the admin interface of
Moodle and set the full path to this file. Please note that the default value
for such a field is this custom_hook.php file itself.

You should not change the name of the funcions since that's the API the plugin
expect to exist and to use.

Read the comments of each function to discover when they are called and what
are they for.
*/


/*
 name: saml_hook_attribute_filter
 arguments:
   - $saml_attributes: array of SAML attributes
 return value:
   - nothing
 purpose: this function allows you to modify the array of SAML attributes.
          You can change the values of them (e.g. removing the non desired
          urn parts) or you can even remove or add attributes on the fly.
*/
function saml_hook_attribute_filter(&$saml_attributes) {

    // Will match this attribute to creators
    if(isset($saml_attributes['distinguishedName'])) {
       // $myvar = print_r($saml_attributes['distinguishedName'],true);
        $myvar = $saml_attributes['distinguishedName'][0];
        $found = strpos($myvar, "OU=Staff"); 
        if($found === false)
        {
           // OU=Staff was not found as part of the Distinguished Name
           $saml_attributes['hcrhsGroup'][0]='Student';
        }
        else{
           $saml_attributes['hcrhsGroup'][0]='Staff'; 
        }
	
    }
}

/*
 name: saml_hook_user_exists
 arguments:
   - $username: candidate name of the current user
   - $saml_attributes: array of SAML attributes
   - $user_exists: true if the $username exists in Moodle database
 return value:
   - true if you consider that this username should exist, false otherwise.
 purpose: this function let you change the logic by which the plugin thinks
          the user exists in Moodle. You can even change the username if
          the user exists but you want to recreate with another name.
*/
function saml_hook_user_exists(&$username, $saml_attributes, $user_exists) {
    return true;
}

/*
 name: saml_hook_authorize_user
 arguments:
    - $username: name of the current user
    - $saml_attributes: array of SAML attributes
    - $authorize_user: true if the plugin thinks this user should be allowed
 return value:
    - true if the user should be authorized or an error string explaining
      why the user access should be denied.
 purpose: use this function to deny the access to the current user based on
          the value of its attributes or any other reason you want. It is
	  very important that this function return either true or an error
	  message.
*/
function saml_hook_authorize_user($username, $saml_attributes, $authorize_user) {
    return true;
}

/*
 name: saml_hook_post_user_create
 arguments:
   - $user: object containing the Moodle user
 return value:
   - nothing
 purpose: use this function if you want to make changes to the user object
          or update any external system for statistics or something similar.
*/
function saml_hook_post_user_created($user) {
     $hcrhsGroup = $user->hcrhsGroup;

     $roles = get_roles_with_capability('moodle/legacy:coursecreator', CAP_ALLOW);
     $creatorrole = array_shift($roles);      // We can only use one, let's use the first one
     $systemcontext = get_context_instance(CONTEXT_SYSTEM);
     if($hcrhsGroup == 'Staff'){ 
         role_assign($creatorrole->id, $user->id, 0, $systemcontext->id, 0, 0, 0, 'saml');
     } else {
       //unassign only if previously assigned by this plugin!
         role_unassign($creatorrole->id, $user->id, 0, $systemcontext->id, 'saml');
     }
 
}

?>