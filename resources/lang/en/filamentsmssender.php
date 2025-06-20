<?php

// translations for HusamTariq/FilamentSmsSender
return [
    // Navigation & Resource Labels
    'navigation_group' => 'SMS Management',
    'navigation_label' => 'SMS Providers',
    'model_label' => 'SMS Provider',
    'plural_model_label' => 'SMS Providers',

    // Page Titles
    'page_title' => 'SMS Providers',
    'add_provider' => 'Add SMS Provider',
    'edit_provider' => 'Edit SMS Provider',
    'create_provider' => 'Create SMS Provider',

    // Form Sections
    'provider_information' => 'Provider Information',
    'provider_information_description' => 'Basic information about the SMS provider',
    'api_configuration' => 'API Configuration',
    'api_configuration_description' => 'Configure the SMS gateway API settings',
    'otp_configuration' => 'OTP Configuration',
    'otp_configuration_description' => 'Configure One-Time Password settings for this provider',
    'success_configuration' => 'Success Configuration',
    'success_configuration_description' => 'Configure the conditions for a successful SMS response',

    // Form Fields
    'provider_name' => 'Provider Name',
    'provider_name_placeholder' => 'e.g., Twilio, Nexmo, AWS SNS',
    'is_active' => 'Active',
    'is_active_help' => 'Inactive providers cannot be used for sending SMS',
    'is_default' => 'Set as Default Provider',
    'is_default_help' => 'Only one provider can be set as default at a time',
    'http_method' => 'HTTP Method',
    'api_endpoint_url' => 'API Endpoint URL',
    'api_endpoint_placeholder' => 'https://api.sms-gateway.com/send',
    'request_parameters' => 'Request Parameters',
    'request_parameters_description' => 'Define parameters to send with each SMS request. Use {{ recipient }} and {{ message }} as placeholders.',
    'parameter_name' => 'Parameter Name',
    'parameter_name_placeholder' => 'to, message, api_key, etc.',
    'parameter_value' => 'Parameter Value',
    'parameter_value_placeholder' => '{{ recipient }}, {{ message }}, your-api-key, etc.',
    'add_parameter' => 'Add Parameter',
    'http_headers' => 'HTTP Headers',
    'http_headers_description' => 'Define custom HTTP headers (e.g., Authorization, Content-Type)',
    'header_name' => 'Header Name',
    'header_name_placeholder' => 'Authorization, Content-Type, etc.',
    'header_value' => 'Header Value',
    'header_value_placeholder' => 'Bearer your-token, application/json, etc.',
    'add_header' => 'Add Header',
    'otp_length' => 'OTP Length',
    'otp_expiry_minutes' => 'Expiry Time (minutes)',
    'otp_template' => 'OTP Message Template',
    'otp_template_placeholder' => 'Your OTP code is: {{ otp_code }}',
    'otp_template_default' => 'Your OTP code is: {{ otp_code }}',
    'otp_template_help' => 'Use {{ otp_code }} as placeholder for the actual OTP code',
    'http_response_body' => 'HTTP Response Body',
    'success_code' => 'Success HTTP Code',
    'success_body' => 'Success Body Value',
    'success_conditional_body' => 'Success Body Condition',
    'success_conditional_equal' => 'Equal',
    'success_conditional_greater' => 'Greater Than',
    'success_conditional_less' => 'Less Than',
    'success_conditional_like' => 'Like',

    // Table Columns
    'table_name' => 'Provider Name',
    'table_endpoint' => 'API Endpoint',
    'table_method' => 'Method',
    'table_default' => 'Default',
    'table_active' => 'Active',
    'table_created' => 'Created',

    // Filters
    'filter_default_provider' => 'Default Provider',
    'filter_active' => 'Active',

    // Actions
    'action_test' => 'Test',
    'action_test_provider' => 'Test Provider',
    'action_make_default' => 'Make Default',
    'action_edit' => 'Edit',
    'action_delete' => 'Delete',

    // Test Form
    'test_phone_number' => 'Test Phone Number',
    'test_phone_placeholder' => '+1234567890',
    'test_message' => 'Test Message',
    'test_message_placeholder' => 'This is a test message',
    'test_message_default' => 'Test message from SMS Provider',

    // Confirmation Modals
    'make_default_heading' => 'Make Default Provider',
    'make_default_description' => 'Are you sure you want to make \':name\' the default SMS provider?',

    // Notifications
    'test_sms_sent_title' => 'Test SMS Sent',
    'test_sms_sent_body' => 'Test SMS has been sent successfully!',
    'test_failed_title' => 'Test Failed',
    'test_failed_body' => 'Failed to send test SMS. Check logs for details.',
    'default_provider_updated_title' => 'Default Provider Updated',
    'default_provider_updated_body' => '\':name\' is now the default SMS provider.',
    'cannot_delete_provider_title' => 'Cannot Delete Provider',
    'cannot_delete_provider_body' => 'This provider cannot be deleted because it\'s the only active provider or has pending OTP codes.',
    'provider_created_title' => 'SMS Provider Created',
    'provider_created_body' => 'The SMS provider has been created successfully.',
    'provider_updated_title' => 'SMS Provider Updated',
    'provider_updated_body' => 'The SMS provider has been updated successfully.',
    'default_provider_set_title' => 'Default Provider Set',
    'default_provider_set_body' => 'This provider has been automatically set as default since it\'s your first provider.',
    'cannot_delete_all_active_title' => 'Cannot Delete All Active Providers',
    'cannot_delete_all_active_body' => 'At least one active SMS provider must remain.',

    // Page Subheadings
    'no_default_provider' => '⚠️ No default provider set. :count active providers.',
    'default_provider_status' => 'Default: :name | :count active providers',

    // Additional notifications and confirmations
    'settings_saved_title' => 'Settings Saved',
    'settings_saved_body' => 'SMS gateway settings have been saved successfully.',
    'test_sms_confirmation' => 'Are you sure you want to send a test SMS? Make sure your gateway configuration is saved first.',
    'test_otp_confirmation' => 'Are you sure you want to send a test OTP? Make sure your gateway configuration is saved first.',
    'test_failed_missing_data' => 'Please enter both recipient and message.',
    'test_sms_failed_body' => 'Failed to send test SMS. Please check your configuration and logs.',
    'test_otp_missing_recipient' => 'Please enter a recipient phone number.',
    'test_otp_sent_title' => 'Test OTP Sent',
    'test_otp_sent_body' => 'Test OTP has been sent successfully! Code: :code',
    'test_otp_failed_body' => 'Failed to send test OTP. Please check your configuration and logs.',

    // OTP Verification Component
    'phone_number' => 'Phone Number',
    'phone_number_placeholder' => '+1234567890',
    'identifier_optional' => 'Identifier (Optional)',
    'identifier_placeholder' => 'e.g., user_registration, password_reset',
    'send_otp' => 'Send OTP',
    'enter_otp_code' => 'Enter OTP Code',
    'otp_code_placeholder' => 'Enter the code you received',
    'verify_otp' => 'Verify OTP',
    'reset' => 'Reset',
    'resend_otp' => 'Resend OTP',
    'verification_successful' => 'Verification Successful!',
    'phone_verified_successfully' => 'Your phone number has been verified successfully.',
    'verify_another_number' => 'Verify Another Number',
    'too_many_otp_requests' => 'Too many OTP requests. Please try again later.',
    'otp_sent_successfully' => 'OTP sent successfully!',
    'failed_to_send_otp' => 'Failed to send OTP. Please try again.',
    'otp_verified_successfully' => 'OTP verified successfully!',
    'invalid_or_expired_otp' => 'Invalid or expired OTP code.',
];
