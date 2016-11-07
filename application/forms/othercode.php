 <tr bgcolor="6D5CDD">
						<td colspan="4" style="text-align: center; color:#fff;background:#6D5CDD;">
							<?php $brachs = explode('/',$keycode['footer_branch']);?>
							<ul style="list-style-type: none;float:left; text-align: left;padding-left:10px;">
								<?php foreach ($brachs AS $key =>$branch):?>
								<li><?php echo $branch;?></li>
							    <?php endforeach;?>
							</ul>
							<?php $phones = explode('/',$keycode['foot_phone']);?>
							<ul style="list-style-type: none;float:left;text-align: left;padding-left:10px;">
								<?php foreach ($phones AS $key =>$phone):?>
								<li><?php echo $phone;?></li>
							    <?php endforeach;?>
							</ul>
							<?php $contacts= explode('/',$keycode['f_email_website']);?>
							<ul style="list-style-type: none;float:left;text-align: left;padding-left:10px;">
								<?php foreach ($contacts AS $key =>$contact):?>
								<li><?php echo $contact;?></li>
							    <?php endforeach;?>
							</ul>
						</td>
				   </tr>