<!DOCTYPE html>
<html lang="ru">
	<head>
		<title>redirect...</title>
		<style>
		* {
			box-sizing: border-box;
		}
		body {
			background:#fafafa;
		}
		.fixed-overlay {
			position: fixed;
			overflow: auto;
			left: 0;
			top: 0;
			width: 100%;
			height: 100%;
		}
		.fixed-overlay__modal {
			text-align: center;
			white-space: nowrap;
		}
		.fixed-overlay__modal::after {
			display: inline-block;
			vertical-align: middle;
			width: 0;
			height: 100%;
			content: '';
		}
		.modal {
			display: inline-block;
			vertical-align: middle;
		}
		.modal_container .title, .modal_container .title a{
			margin-top:10px;
			color:#356aa0;
			font-family: monospace;
			text-decoration:none;
		}
		.modal_container .title a:hover{
			text-decoration:underline;
		}
		.modal_container .title i{
			font-size:75%;
		}
		.spinner {
			margin: 0 auto;
			width: 50px;
			height: 30px;
			text-align: center;
			font-size: 10px;
		}
		.spinner > div {
			background-color: #356aa0;
			margin: 0 1px;
			height: 100%;
			width: 6px;
			display: inline-block;
			-webkit-animation: stretchdelay 1.2s infinite ease-in-out;
			animation: stretchdelay 1.2s infinite ease-in-out;
		}
		.spinner .rect2 {
			-webkit-animation-delay: -1.1s;
			animation-delay: -1.1s;
		}
		.spinner .rect3 {
			-webkit-animation-delay: -1.0s;
			animation-delay: -1.0s;
		}
		.spinner .rect4 {
			-webkit-animation-delay: -0.9s;
			animation-delay: -0.9s;
		}
		.spinner .rect5 {
			-webkit-animation-delay: -0.8s;
			animation-delay: -0.8s;
		}
		@-webkit-keyframes stretchdelay {
			0%, 40%, 100% { -webkit-transform: scaleY(0.4) }
			20% { -webkit-transform: scaleY(1.0) }
		}
		@keyframes stretchdelay {
			0%, 40%, 100% {
				transform: scaleY(0.4);
				-webkit-transform: scaleY(0.4);
			}  20% {
				transform: scaleY(1.0);
				-webkit-transform: scaleY(1.0);
			}
		}
		@-webkit-keyframes expand {
			0% { -webkit-transform: scale3d(1,0,1); }
			25% { -webkit-transform: scale3d(1,1.2,1); }
			50% { -webkit-transform: scale3d(1,0.85,1); }
			75% { -webkit-transform: scale3d(1,1.05,1) }
			100% { -webkit-transform: scale3d(1,1,1); }
		}
		@keyframes expand {
			0% { -webkit-transform: scale3d(1,0,1); transform: scale3d(1,0,1); }
			25% { -webkit-transform: scale3d(1,1.2,1); transform: scale3d(1,1.2,1); }
			50% { -webkit-transform: scale3d(1,0.85,1); transform: scale3d(1,0.85,1); }
			75% { -webkit-transform: scale3d(1,1.05,1); transform: scale3d(1,1.05,1); }
			100% { -webkit-transform: scale3d(1,1,1); transform: scale3d(1,1,1); }
		}
		@-webkit-keyframes bounce {
			0% { -webkit-transform: translate3d(0,-25px,0); opacity:0; }
			25% { -webkit-transform: translate3d(0,10px,0); }
			50% { -webkit-transform: translate3d(0,-6px,0); }
			75% { -webkit-transform: translate3d(0,2px,0); }
			100% { -webkit-transform: translate3d(0,0,0); opacity: 1; }
		}
		@keyframes bounce {
			0% { -webkit-transform: translate3d(0,-25px,0); transform: translate3d(0,-25px,0); opacity:0; }
			25% { -webkit-transform: translate3d(0,10px,0); transform: translate3d(0,10px,0); }
			50% { -webkit-transform: translate3d(0,-6px,0); transform: translate3d(0,-6px,0); }
			75% { -webkit-transform: translate3d(0,2px,0); transform: translate3d(0,2px,0); }
			100% { -webkit-transform: translate3d(0,0,0); transform: translate3d(0,0,0); opacity: 1; }
		}
		</style>
	</head>
	<body>
		<div class="fixed-overlay fixed-overlay__modal">
			 <div class="modal">
				<div class="modal_container"> 
					<div class="spinner" style="display: block;"><div class="rect1"></div><div class="rect2"></div><div class="rect3"></div><div class="rect4"></div><div class="rect5"></div></div>
					<div class="title"><a href="https://wordpress.org/plugins/wgapiauth/" target="_blank">WG API Auth</a><br><i><a href="http://worldoftanks.ru/community/accounts/422766/" target="_blank">© STREJlA</a></i></div>
				</div>
			 </div>
		</div>
	</body>
</html>