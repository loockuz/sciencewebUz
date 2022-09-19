{**
 * plugins/generic/sciencewebUz/templates/settingsForm.tpl

 * Copyright (c) 2022 I-EDU GROUP by Dilshod Tursimatov
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * Web feeds plugin settings
 *
 *}
<script>
	$(function() {ldelim}
		// Attach the form handler.
		$('#sciencewebUzSettingsForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form class="pkp_form" id="sciencewebUzSettingsForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT op="manage" category="generic" plugin=$pluginName verb="settings" save=true}">
	<div id="sciencewebUzSettings">

		<div id="description">
			Platforma bilan integratsiya qilishingiz uchun <a href="https://scienceweb.uz/">https://scienceweb.uz/</a> ga murojaat qiling.
		</div>

		{csrf}
		{include file="controllers/notification/inPlaceNotification.tpl" notificationId="sciencewebUzSettingsFormNotification"}

		{fbvFormArea id="sciencewebUzSettingsFormArea"} 
			{fbvElement type="text" id="sciencewebUzToken" value=$sciencewebUzToken label="SciencewebUZ token" size=$fbvStyles.size.SMALL}
		{/fbvFormArea}

		{fbvFormButtons}
		<p><span class="formRequired">{translate key="common.requiredField"}</span></p>
	</div>
</form>
