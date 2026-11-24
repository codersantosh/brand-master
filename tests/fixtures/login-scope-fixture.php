<?php
/**
 * Fixture mimicking the bare variable reads in wp-login.php.
 *
 * Intentionally contains NO `global` declarations: when included from inside
 * a class method, bare reads resolve against the method's local symbol table —
 * exactly like wp-login.php's `$user_login` (form value) and `$error`
 * (autofocus check), plus the `$action` / `$interim_login` assignments that
 * `login_header()` / `login_footer()` later read via `global`.
 *
 * @package Brand_Master
 */

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals, Squiz.Commenting.FileComment.Missing, Squiz.Commenting.FunctionComment.Missing, Generic.CodeAnalysis.UnusedFunctionParameter, WordPress.WP.GlobalVariablesOverride

// Bare reads: warn with "Undefined variable" if the including method scope
// did not alias these symbols to $GLOBALS (the bug this guards against).
$rendered_user = $user_login;
$can_autofocus = ! $error;

// Assignments: must propagate to $GLOBALS for login_header()/login_footer().
$action        = 'custom_action';
$interim_login = 'success';
