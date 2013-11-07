<div class="itemInfo">
  <table class="iteminfo" width="100%" cellSpacing="5px">
    <tr>
      <td colspan=2 class="itemName" align="center" valign="top">
        <a id='itemName' href="?id=<?= $this->id; ?>"><?= $this->name; ?></a> <?= $this->showinfoLink; ?>
      </td>
    </tr>
    <tr>
      <td class="itemDesc" valign="top">
        <?= $this->icon; ?> <?= $this->desc; ?>
      </td>
      <td valign="top" align='right' >
        <? if($this->loggedin) $this->buyItem->render(); ?>
      </td>
    </tr>
  </table>
</div>