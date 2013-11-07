<table class="buydial" width="420px">
  <tr>
    <th colspan=2 >buy <?= $this->name; ?></th>
  </tr>
  <tr>
    <td colspan=2 class="itemName"><?= $this->sicon; ?> <?= $this->name; ?></td>
  </tr>
  <tr>
    <td width='100px'>Location</td>
    <td>>> Misneden VI - Moon 42 - Federal Bureau dans ton cul <<</td>
  </tr>
  <tr>
    <td width='100px'>Price</td>
    <td><?= $this->pcost; ?> [ <span class='<?= $this->typereduc; ?>'><?= $this->preduc; ?></span> ]</td>
  </tr>
  <tr>
    <td>Best regional</td>
    <td><?= $this->pcostSell; ?></td>
  </tr> 
  <tr>
    <td>Quantity</td>
    <td><input type='text' id='quantity' value='1' onchange="javascript:calcPrice('<?= $this->cost; ?>');" /></td>
  </tr>
  <tr>
    <td>Total</td>
    <td id='totalPrice' class="<?= $this->typereduc; ?> total"><?= $this->pcost; ?></td>
  </tr>
  <tr>
    <td class="options" colspan=2>
      <input id='corp' type=checkbox <?= $this->chkCorp; ?>/> USE CORP ACCOUNT
      <input id='corpOnly' type=checkbox <?= $this->chkCorpOnly; ?>/> YOUR CORP ONLY
    </td>
  </tr>
  <tr>
    <td colspan=2 class="buttonbar">
    <?= ($this->costSell ? "<input type=button value='BUY' onclick=\"javascript:basketAdd('".$this->id."','".$this->cost."');\" />" : "Sur Devis!") ?>
    </td>
  </tr>
</table>