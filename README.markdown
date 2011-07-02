CodeIgniter-Filter
==================

CodeIgniter-Filter adds basic before_filter and after_filter
functionality to controllers in CodeIgniter applications using a custom
controller.

I've opted to ditch the hooks method due to not being able to have a
protected or private visability on controller methods used as callbacks.

All you need to do is extend from MY_Controller instead of CI_Controller
and the functionality will be available.

Notes
_____

Compatible with PHP5 and CI2.0+
