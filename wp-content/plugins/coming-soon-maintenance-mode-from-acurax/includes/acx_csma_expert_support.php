<?php
if(ISSET($_SERVER['HTTP_HOST']))
{
$acx_installation_url = $_SERVER['HTTP_HOST'];
} else
{
$acx_installation_url = "";
}
?>
<div class="acx_csma_es_common_raw acx_csma_es_common_bg">
	<div class="acx_csma_es_middle_section">
    
    <div class="acx_csma_es_acx_content_area">
    	<div class="acx_csma_es_wp_left_area">
        <div class="acx_csma_es_wp_left_content_inner">
        	<div class="acx_csma_es_wp_main_head"><?php _e("Do you Need Technical Support Services to Get the Best Out of Your Wordpress Site ?",ACX_CSMA_WP_SLUG);?></div> <!-- wp_main_head -->
            <div class="acx_csma_es_wp_sub_para_des"><?php _e("Acurax offer a number of WordPress related services: Form installing WordPress on your domain to offering support for existing WordPress sites.",ACX_CSMA_WP_SLUG);?></div> <!-- acx_csma_es_wp_sub_para_des -->
            <div class="acx_csma_es_wp_acx_service_list">
            	<ul>
                <li><?php _e("Troubleshoot WordPress Site Issues",ACX_CSMA_WP_SLUG);?></li>
                    <li><?php _e("Recommend & Install Plugins For Improved WordPress Performance",ACX_CSMA_WP_SLUG);?></li>
                    <li><?php _e("Create, Modify, Or Customise, Themes",ACX_CSMA_WP_SLUG);?></li>
                    <li><?php _e("Explain Errors And Recommend Solutions",ACX_CSMA_WP_SLUG);?></li>
                    <li><?php _e("Custom Plugin Development According To Your Needs",ACX_CSMA_WP_SLUG);?></li>
                    <li><?php _e("Plugin Integration Support",ACX_CSMA_WP_SLUG);?></li>
                    <li><?php _e("Many ",ACX_CSMA_WP_SLUG);?><a href="http://wordpress.acurax.com/?utm_source=csma&utm_campaign=expert_support" target="_blank"><?php _e("More...",ACX_CSMA_WP_SLUG);?></a></li>
               </ul>
            </div> <!-- acx_csma_es_wp_acx_service_list -->
            
   <div class="acx_csma_es_wp_send_ylw_para"><?php _e("We Have Extensive Experience in WordPress Troubleshooting,Theme Design & Plugin Development.",ACX_CSMA_WP_SLUG);?></div> <!-- acx_csma_es_wp_secnd_ylw_para-->
   
        </div> <!-- acx_csma_es_wp_left_content_inner -->
        </div> <!-- acx_csma_es_wp_left_area -->
        
        <div class="acx_csma_es_wp_right_area">
        	<div class="acx_csma_es_wp_right_inner_form_wrap">
            	<div class="acx_csma_es_wp_inner_wp_form">
                <div class="acx_csma_es_wp_form_head"><?php _e("WE ARE DEDICATED TO HELP YOU. SUBMIT YOUR REQUEST NOW..!",ACX_CSMA_WP_SLUG);?></div> <!-- acx_csma_es_wp_form_head -->
                <form class="acx_csma_es_wp_support_acx">
                <span class="acx_csma_es_cnvas_input acx_csma_es_half_width_sec acx_csma_es_haif_marg_right"><input type="text" placeholder="<?php _e('Name',ACX_CSMA_WP_SLUG);?>" id="acx_name"></span> <!-- acx_csma_es_cnvas_input -->
                <span class="acx_csma_es_cnvas_input acx_csma_es_half_width_sec acx_csma_es_haif_marg_left"><input type="email" placeholder="<?php _e('Email',ACX_CSMA_WP_SLUG);?>" id="acx_email"></span> <!-- acx_csma_es_cnvas_input -->
                <span class="acx_csma_es_cnvas_input acx_csma_es_half_width_sec acx_csma_es_haif_marg_right"><input type="text" placeholder="<?php _e('Phone Number',ACX_CSMA_WP_SLUG);?>" id="acx_phone"></span> <!-- acx_csma_es_cnvas_input -->
                <span class="acx_csma_es_cnvas_input acx_csma_es_half_width_sec acx_csma_es_haif_marg_left"><input type="text" placeholder="<?php _e('Website Url',ACX_CSMA_WP_SLUG);?>" value="<?php echo $acx_installation_url; ?>" id="acx_weburl"></span> <!-- acx_csma_es_cnvas_input -->
                <span class="acx_csma_es_cnvas_input"><input type="text" placeholder="<?php _e('Subject',ACX_CSMA_WP_SLUG);?>" id="acx_subject"></span> <!-- acx_csma_es_cnvas_input -->
                <span class="acx_csma_es_cnvas_input"><textarea placeholder="<?php _e('Question',ACX_CSMA_WP_SLUG);?>" id="acx_question"></textarea></span> <!-- acx_csma_es_cnvas_input -->
                <span class="acx_csma_es_cnvas_input"><input class="acx_csma_es_wp_acx_submit" type="button" value="<?php _e('SUBMIT RQUEST',ACX_CSMA_WP_SLUG);?>" onclick="acx_csma_quick_request_submit();"></span> <!-- acx_csma_es_cnvas_input -->
                </form>
                </div> <!-- acx_csma_es_wp_inner_wp_form -->
            </div> <!-- acx_csma_es_wp_right_inner_form_wrap -->
        </div> <!-- acx_csma_es_wp_left_area -->
    </div> <!-- acx_csma_es_acx_content_area -->
    
    <div class="acx_csma_es_footer_content_cvr">
    <div class="acx_csma_es_wp_footer_area_desc"><?php _e("Its our pleasure to thank you for using our plugin and being with us. We always do our best to help you on your needs. If you like to hide this menu, you can do so at ",ACX_CSMA_WP_SLUG);?><a href="<?php echo wp_nonce_url(admin_url('admin.php?page=Acurax-Coming-Soon-Maintenance-Mode-Misc'));?>"><?php _e("Misc",ACX_CSMA_WP_SLUG);?></a><?php _e(" page which is under our plugin options.",ACX_CSMA_WP_SLUG);?></div> <!--acx_csma_es_wp_footer_area_desc -->
    </div> <!-- acx_csma_es_footer_content_cvr -->
    
    </div> <!-- acx_csma_es_middle_section -->
</div> <!--acx_csma_es_common_raw -->
<script type="text/javascript">
var request_acx_form_status = 0;
function acx_quick_form_reset()
{
	jQuery("#acx_subject").val('');
	jQuery("#acx_question").val('');
}
acx_quick_form_reset();
function acx_csma_quick_request_submit()
{
	var acx_name = jQuery("#acx_name").val();
	var acx_email = jQuery("#acx_email").val();
	var acx_phone = jQuery("#acx_phone").val();
	var acx_weburl = jQuery("#acx_weburl").val();
	var acx_subject = jQuery("#acx_subject").val();
	var acx_question = jQuery("#acx_question").val();
	var order = '&action=acx_csma_quick_request_submit&acx_name='+acx_name+'&acx_email='+acx_email+'&acx_phone='+acx_phone+'&acx_weburl='+acx_weburl+'&acx_subject='+acx_subject+'&acx_question='+acx_question+'&acx_csma_es=<?php echo wp_create_nonce("acx_csma_es"); ?>';  
	if(request_acx_form_status == 0)
	{
		request_acx_form_status = 1;
		jQuery.post(ajaxurl, order, function(quick_request_acx_response)
		{
		if(quick_request_acx_response == 1)
		{
			alert('<?php _e("Your Request Submitted Successfully!",ACX_CSMA_WP_SLUG);?>');
			acx_quick_form_reset();
			request_acx_form_status = 0;
		} else if(quick_request_acx_response == 2)
		{
			alert('<?php _e("Please Fill Mandatory Fields.",ACX_CSMA_WP_SLUG);?>');
			request_acx_form_status = 0;
		} else
		{
			alert('<?php _e("There was an error processing the request, Please try again.",ACX_CSMA_WP_SLUG);?>');
			acx_quick_form_reset();
			request_acx_form_status = 0;
		}
		});
	} else
	{
		alert('<?php _e("A request is already in progress.",ACX_CSMA_WP_SLUG);?>');
	}
}
</script>