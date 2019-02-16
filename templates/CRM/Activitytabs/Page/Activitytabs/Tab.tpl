{* This produces the markup for each of the activitytabs tabs *}
<table>
  <thead>
    <tr>
      {foreach from=$columnMap item=col key=i}
        <th>{$col}</th>
      {/foreach}
        <th>View</th>
    </tr>
  </thead>
  <tbody>
    {foreach from=$activities name=activityloop key=i item=activity}
    <tr>
      {foreach from=$atabConfig->columns item=col key=j}
        <td>{$activity[$col]}</td>
      {/foreach}
      <td>
        <a href="/civicrm/activity?action=view&reset=1&id={$activity.id}&cid={$contactId}&context=activity&searchContext=activity" >View</a>
      </td>
    </tr>
    {/foreach}
  </tbody>
</table>
