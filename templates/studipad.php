<?php
if (isset($error)) {
    echo MessageBox::error($error);
}

if (isset($message)) {
    echo MessageBox::info($message);
}

if (!isset($padurl)) {
    if (isset($tpads)) {
?>
    <h1>Stud.IPads</h1>

    <table style="width: 100%;">
        <colgroup>
            <col width="45%" />
            <col width="30%" />
            <col width="10%" />
            <col width="15%" />
        </colgroup>

        <?php foreach ($tpads as $padid => $pad) {
            $stylecolor = TextHelper::cycle('background-color:#F2F2F2 ', 'background-color:#E2E2E2 ');
            echo "<tr  style=\"$stylecolor\">"; ?>
            <td>
                <?php
                echo '<b>'.$pad['title'].'</b>';

                if ($pad['new']) {
                    echo ' (<span style="color: red">'.dgettext('studipad', 'neu').'</span>)';
                }

                if ($pad['public']) {
                    echo ' ('.dgettext('studipad', '&ouml;ffentlich').')';
                }

                //INSERTED BY EL 13.11.2014
                if ($pad['readOnly']) {
                    echo ' ('.dgettext('studipad', 'Schreibgesch&uuml;tzt').')';
                }
                //INSERTED END

                if ($pad['hasPassword']) {
                    echo '<img border="0" src="'.Assets::image_path('icons/16/red/lock-locked.png').'"'.tooltip(dgettext('studipad', 'Das Pad ist mit einem Passwort versehen.')).'/>';
                } ?>
            </td>

            <td style="text-align: right; padding-right: 5px;">
                <?php
                if ($pad['lastEdited']) {
                    echo '<b>'.dgettext('studipad', 'letzte &Auml;nderung').':</b> '.strftime('%x, %H:%M', $pad['lastEdited']);
                } ?>
            </td>

            <?php if (!(isset($padadmin) && $padadmin)) { ?>
                <td></td>
            <?php } ?>

            <td align="center">
                <a id="start<?= $padid; ?>" href="<?= PluginEngine::getLink('studipadplugin', array('action' => 'open', 'pad' => $padid)); ?>">
                    <?= Icon::create(
                        'door-enter',
                        'clickable'
                    )->asImg(
                        '16px',
                        [
                            'title' => $pad['title'],
                        ]
                    ); ?>
                </a>
            </td>

            <?php if (isset($padadmin) && $padadmin) {
            ?>
                <td align="center">
                    <?=Studip\Button::create(dgettext('studipad', 'Einstellungen'), '', array('class' => "studipadpluginclick$padid")); /*CHANGED 11.12.2014 */ ?>
                </td>
            <?php
            } ?>

</tr>

<?php //Adminbereich in einer eigenen Table und fÃ¼r jedes Element wird eine Form ausgegeben
if (isset($padadmin) && ($padadmin)) {
    echo "<tr class=\"studipadpluginadm$padid\" style=\"display:none;\">";
    echo '<td colspan="4">';
    echo '<form action="'.PluginEngine::getLink('studipadplugin', array('pad' => $padid)).'" method="POST" style="width: 100%;">';

    echo "<input type=\"hidden\" name=\"padid\" value=\"$padid\" />"; ?>
    <table style="width: 100%;">

        <?="<tr class=\"studipadpluginadm$padid\" style=\"display: none;\">"; ?>
        <?="<td colspan=\"3\" style=\"$stylecolor\">"; ?>
        <?='<b>'.dgettext('studipad', 'Funktionen').'</b>'; ?>
            </td>
            </tr>
            <?="<tr class=\"studipadpluginadm$padid\" style=\"display: none;\">";

            echo "<td align=\"left\" style=\"$stylecolor\">"; ?>
            <table>
                <tr>
                    <td>
                        <?='<input type="checkbox" name="showControls" value="true"'.(($pad['showControls']) ? 'checked="checked"' : '').' /> '.dgettext('studipad', 'Kontrollelemente anzeigen'); ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <?='<input type="checkbox" name="showColorBlock" value="true"'.(($pad['showColorBlock'] && $pad['showControls']) ? 'checked="checked"' : '').((!$pad['showControls']) ? 'disabled="disabled"' : '').' /> '.dgettext('studipad', 'Farbsteuerelemente anzeigen'); ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <?='<input type="checkbox" name="showImportExportBlock" value="true"'.(($pad['showImportExportBlock'] && $pad['showControls']) ? 'checked="checked"' : '').((!$pad['showControls']) ? 'disabled="disabled"' : '').' /> '.dgettext('studipad', 'Import- Exportelemente ein- oder ausblenden'); ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <?='<input type="checkbox" name="showChat" value="true"'.(($pad['showChat'] && $pad['showControls']) ? 'checked="checked"' : '').((!$pad['showControls']) ? 'disabled="disabled"' : '').' /> '.dgettext('studipad', 'Chat anzeigen'); ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <?='<input type="checkbox" name="showLineNumbers" value="true"'.(($pad['showLineNumbers'] && $pad['showControls']) ? 'checked="checked"' : '').((!$pad['showControls']) ? 'disabled="disabled"' : '').' /> '.dgettext('studipad', 'Zeilennummern anzeigen'); ?>
                    </td>
                </tr>
            </table>
            </td>

            <?="<td align=\"right\" style=\"$stylecolor; width: 300px;\">"; ?>
            &nbsp;
            </td>

            <?="<td style=\"$stylecolor\">"; ?>
            &nbsp;
            </td>
            </tr>


            <?="<tr class=\"studipadpluginadm$padid\" style=\"display: none;\">"; ?>
            <?="<td colspan=\"3\" style=\"$stylecolor\">"; ?>
            <?='<b>'.dgettext('studipad', 'Schutz und Sichtbarkeit au&szlig;erhalb Stud.IP').'</b>'; ?>
            </td>
            </tr>

            <?="<tr class=\"studipadpluginadm$padid\" style=\"display: none;\">"; ?>
            <?="<td style=\"$stylecolor\">"; ?>

            <?='<input type="checkbox" name="ReadOnly" value="1"'.(($pad['readOnly']) ? 'checked="checked"' : '').'  />'.dgettext('studipad', 'Schreibrechte entziehen (Schreibschutz)'); ?>

            </td>

            <?="<td align=\"right\" style=\"$stylecolor; vertical-align: middle;\">";

            if ($pad['hasPassword']) {
                echo Studip\LinkButton::create(dgettext('studipad', 'Passwort löschen'), PluginEngine::getURL('studipadplugin', array('action' => 'unset_password', 'pad' => $padid))); //CHANGED EL 11.12.2104
            }

            if (!($pad['hasPassword'])) {
                echo '<b>'.dgettext('studipad', 'Passwort').':</b> <input type="password" name="pad_password" value="" size="10" maxlength="32" />';

                echo '&nbsp;'.Studip\Button::create(dgettext('studipad', 'zuweisen'), 'set_pad_password').'&nbsp;'; //CHANGED 11.12.2014

                $tooltip = dgettext('studipad', 'Wenn ein Passwort gesetzt wird kann das Pad nur nach Eingabe des Passwortes verwendet werden.'); //CHANGED EL 11.12.2014
                //echo Assets::img('icons/16/blue/info.png', array('style' => "vertical-align: middle;", 'title' => $tooltip, 'onClick' => "alert('$tooltip');")); //CHANGED 11.12.2014
                echo Icon::create('info-circle', 'clickable')->asImg('16px', ['title' => $tooltip, 'onClick' => "alert('$tooltip');"]);
            } ?>
            </td>

            <?="<td align=\"right\" style=\"$stylecolor; vertical-align: middle;\">";
            if ($pad['public']) {
                echo Studip\LinkButton::create(dgettext('studipad', 'Ver&ouml;ffentlichung beenden'), PluginEngine::getURL('studipadplugin', array('action' => 'unset_public', 'pad' => $padid))); //CHANGED EL 11.12.2104

                $tooltip = dgettext('studipad', '&#214;ffentliche Pads k&#246;nnen ohne Stud.IP-Zugang von jedem verwendet werden.'); //CHANGED EL 11.12.2014
                //echo Assets::img('icons/16/blue/info.png', array('style' => "vertical-align: middle;", 'title' => $tooltip, 'onClick' => "alert('$tooltip');")); //CHANGED EL 11.12.2014
                echo Icon::create('info-circle', 'clickable')->asImg('16px', ['title' => $tooltip, 'onClick' => "alert('$tooltip');"]);
            }

            if (!($pad['public'])) {
                echo Studip\LinkButton::create(dgettext('studipad', 'Pad veröffentlichen'), PluginEngine::getURL('studipadplugin', array('action' => 'set_public', 'pad' => $padid))); //CHANGED EL 11.12.2104

                $tooltip = dgettext('studipad', '&#214;ffentliche Pads k&#246;nnen ohne Stud.IP-Zugang von jedem verwendet werden.'); //CHANGED EL 11.12.2014
                //echo Assets::img('icons/16/blue/info.png', array('style' => "vertical-align: middle;", 'title' => $tooltip, 'onClick' => "alert('$tooltip');")); //CHANGED EL 11.12.2014   />";
                echo Icon::create('info-circle', 'clickable')->asImg('16px', ['title' => $tooltip, 'onClick' => "alert('$tooltip');"]);
            } ?>
            </td>
            </tr>
            <?="<tr class=\"studipadpluginadm$padid\" style=\"display: none;\">";

            echo "<td align=\"left\" style=\"$stylecolor\">";
            echo Studip\Button::create(dgettext('studipad', 'speichern'), 'pad_controls_toggle'); //CHANGED 11.12.2014 ?>
            </td>
            <?="<td style=\"$stylecolor\">"; ?>
            &nbsp;
            </td>
            <?="<td align=\"right\" style=\"$stylecolor\">"; ?>
            <?=Studip\LinkButton::create(dgettext('studipad', 'Pad löschen'), PluginEngine::getURL('studipadplugin', array('action' => 'delete', 'pad' => $padid))); ?>
            </td>
            </tr>
    </table>
    </form>
            </td>
            </tr>
<?php
}
} ?>      </table>

<br />


        <?php
        }

        if (isset($padadmin) && $padadmin) {
            echo '<form action="'.PluginEngine::getLink('studipadplugin', array('pad' => $padid)).'" method="POST" style="width: 100%;">';
            echo '<b>'.dgettext('studipad', 'Name des neuen Pads').":</b> <input type=\"text\" name=\"new_pad_name\" value=\"$newPadName\" size=\"32\" maxlength=\"32\" />";
            echo '&nbsp;'.Studip\Button::create(dgettext('studipad', 'anlegen'), 'new_pad'); //CHANGED 11.12.2014
            echo "\n";
            echo '</form>';
        }
        }

        if (isset($padurl)) {
            echo "<h2>$padname</h2>";

            echo "<iframe src=\"$padurl\" width=\"100%\" height=\"400\"></iframe>";
        }

        if (isset($tpads)) {
            echo '<script>';
            foreach ($tpads as $padid => $pad) {
                echo "\n jQuery('.studipadpluginclick$padid').click(function() {
		jQuery('.studipadpluginadm$padid').toggle( \"showOrHide\" );
	});\n";
            }
            echo "</script>\n";
        }
