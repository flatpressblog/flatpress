 
			</div>
			<!-- end #outer-container -->

			{if !isset($smarty.const.MOD_ADMIN_PANEL)}
				{include file="widgetsbottom.tpl"}
			{/if}

			<!-- beginn of #footer -->
			<div id="footer">
				{action hook=wp_footer}
				<p>
				Powered by <a href="https://github.com/flatpressblog/flatpress" target="_blank" rel="noopener noreferrer">FlatPress</a>.
				</p>
			</div>
			<!-- end of #footer -->

		</div>
		<!-- end #body-container -->
		{action hook=end_footer}
	</body>
</html>
