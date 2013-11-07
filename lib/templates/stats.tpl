<table class='mainframe'>
    <tr>
        <td class="headerOrder">Ships Sold</td>
        <td class="headerOrder" >Modules Sold</td>
        <td class="headerOrder">LeaderBoard</td>
    </tr>
    <tr>
        <td rowspan='3' class='details' valign='top' align='center'>
            <?= $this->ShipList; ?>
            For a total of <?= $this->nbShip; ?> ships dispatched in <?= $this->nbOShip; ?> orders
        </td>
        <td class='details' margin='0' padding='0' valign='top' align='center'>
            <?= $this->ModTechList; ?>
            For a total of <?= $this->nbMod; ?> modules<br/>dispatched in <?= $this->nbOMod; ?> orders
        </td>
        <td rowspan='3' class='details' valign='top' >
            <table width='370px' class='iteminfo'>
                <tr>
                    <td class="headerOrder" align='center' >Top Sellers</td>
                    <td class="headerOrder" align='center' >Top Buyers</td>
                </tr>
                <tr>
                    <td width='50%' valign='top'>
                        <div class='leaderboard'><?= $this->LadderC; ?></div>
                    </td>
                    <td width='50%' valign='top'>
                        <div class='leaderboard'><?= $this->LadderP; ?></div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td class="headerOrder" >King of the Hill in <?= $this->lastmonth ?></td>
    </tr>
    <tr>
        <td class='details' margin='0' padding='0' valign='top' align='center'>
            <table  class='iteminfo'>
                <tr>
                    <td class="headerOrder" align='center' >Top Buyers</td>
                    <td class="headerOrder" align='center' >Top Sellers</td>
                </tr>
                <tr>
                    <td width='50%' align='center'><?= $this->TopBuyer; ?></td>
                    <td width='50%' align='center'><?= $this->TopSeller; ?></td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td class='details' colspan=3 align='center'>
            Total production : <b><?= $this->GTitem; ?></b> items manufactured for <b><?= $this->GTorder; ?></b> orders -
            <?= $this->InProgress; ?> orders in progress, <?= $this->OnHold; ?> waiting...
        </td>
    </tr>
</table>