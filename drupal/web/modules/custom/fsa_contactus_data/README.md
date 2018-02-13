### FSA Contact us data

This is a GDPR related module to remove privacy information from webform submissions.

* Add "FSA Contact Us form data handler" as a handler to webform configuration.
  * The handler assumes fields with key `name`, `email`, `address` and  `phone` as personal data by default 
  * The values can be overridden/set in settings.php like this:  
  ```
  $config['fsa_contactus_data']['excluded_field_names'] = ['name', 'email', 'address', 'phone', 'mobile_phone', 'first_name', 'last_name']
  ```

  * Fields that contain personal data can also be identified with a custom property `#personal_data: true` of the element. Example in form build yml format
  ```  
  telephone:
   '#type': tel
   '#title': 'Phone number'
   '#personal_data': true
   ```
