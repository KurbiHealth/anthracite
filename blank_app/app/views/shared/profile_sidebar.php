<?php $user = currentUser(); ?>
<div id="interior-sidebar">
	<div class="sidebar-header">
		<img src="<?php 
			if($user['image_file_name'] == '')
				echo 'http://placehold.it/60x53';
			else
				echo ROOT_URL.'/user_images/'.$user['image_file_name'];
		?>" align="left">
		<h2><?php 
			if($user == '' || $user == FALSE){
				echo '';
			}else{
				echo $user['first_name'].' '.$user['last_name'];
			}
		?></h2>
		<p><a href="<?php echo ROOT_URL; ?>/kurbi/show_profile">view my profile page</a></p>
	</div><!-- END .sidebar-header -->
	<div class="sidebar-nav">
		<ul>
			<li><a href="<?php echo ROOT_URL; ?>/kurbi/show_profile">Profile Settings</a></li>
			<li><a href="<?php echo ROOT_URL; ?>/careteam/home">Care Team</a></li>
			<li><a href="<?php echo ROOT_URL; ?>/care_plan/medications">Medications</a></li>
			<li><a href="<?php echo ROOT_URL; ?>/care_plan/exercises">Exercises</a></li>
			<li><a href="<?php echo ROOT_URL; ?>/care_plan/othertreatments">Other Treatments</a></li>
			<!--<li><a href="<?php echo ROOT_URL; ?>/calendar/home">Calendar</a></li>-->
		</ul>
	</div><!-- END .sidebar-nav -->
</div><!-- END #interior-sidebar -->