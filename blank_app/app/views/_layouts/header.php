<?php $user = currentUser(); ?>
 
<?php if($user == FALSE){ ?>
<header id="home-header">
    <div id="home-header-container">
    	<a href="/"><div id="home-logo"></div></a>
    	<nav class="home-nav">
    		<ul>
    			<li><span style="position:relative;top: -3px;font-size: 10px;color: #666666;">Already Have An Account?</span></li>
    			<li><a href="/site/sign_in" style="position: relative;top: -5px;color: #CC3333;">Sign In</a></li>
    		</ul>
    	</nav>
    	<nav class="home-nav">
			<ul>
				<li><a href="/site/sign_up" id="home-try-btn"></a></li>
			</ul>
		</nav><!-- END #home-nav -->
	</div><!-- END #home-header-container -->		
</header><!-- END #home-header -->
<?php }else{ ?>
<header id="interior-header">
	<div id="interior-header-container">
  		<a href="/communications/home"><div id="interior-logo"></div></a> 
 		<nav id="main-interior-nav">
 			<ul>
 				<li><a href="/daily_journal/home" class="health-journal border-left">Health Journal</a></li>
				<li><a href="/health_chart/home" class="health-chart">Health Chart</a></li>
				<li><a href="/care_plan/medications" class="care-plan">Care Plan</a></li>
				<!--<li><a href="/communications/home" class="care-plan">Communicate</a></li>-->
				<!--<li><a href="/kurbi/care_team_invite" class="care-team">Care Team</a></li>-->
				<!--<li><a href="/calendar/home" class="calendar">Calendar</a></li>-->
 			</ul>
 		</nav>
		<nav id="interior-nav">
			<ul>
		  		<li><a href="/site/log_out" class="btn btn-success">Sign out</a></li>
			</ul>
			<ul>
				<li><a href="/kurbi/show_profile" class="profile main_nav_li">Profile</a></li>
			</ul>
		</nav><!-- END #interior-nav -->

	  	<!-- FORM FOR SEARCHING/ADDING SYMPTOMS
		<form class="navbar-search" action="/kurbi/search_results" method="post" name="navbar-search-form" id="navbar-search-form">
	  		<input type="text" class="search-query" name="searchPhrase" value="" placeholder="search for a symptom or treatment" />
	  	</form>				    
		-->
		
	</div><!-- END #interior-header-container -->
</header><!-- END #interior-header -->
<?php } ?>