# Activity Tabs

This extension provides an easy way to get summaries of the activities that are important to your organisation when viewing a contact record.

Features:

- It allows you to easily set up new **tabs** which show only activities of certain types.

- You can choose which fields from the activity to display in the table, including custom data.

See below for usage.

![Screenshot](/images/screenshot-tab.png)


The extension is licensed under [AGPL-3.0](LICENSE.txt).

## Requirements

* PHP v7.0+
* CiviCRM 5.9+

## Installation (Web UI)

This extension has not yet been published for installation via the web UI.

## Installation (CLI, Zip)

Sysadmins and developers may download the `.zip` file for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
cd <extension-dir>
cv dl activitytabs@https://github.com/FIXME/activitytabs/archive/master.zip
```

## Installation (CLI, Git)

Sysadmins and developers may clone the [Git](https://en.wikipedia.org/wiki/Git) repo for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
git clone https://github.com/FIXME/activitytabs.git
cv en activitytabs
```

## Why?

Activities are one of the most useful features of CiviCRM but the activities list often has too much detail; too many "bulk emails" and other things of minor importance. Also, the fields shown in the activity summary don't always give you the overview you need.

Real world examples:

- A charity providing therapy to children wants to see all the assessment and treatment records for a service user. They want to see the child's name (a custom field), who is assigned and when it is.

- An organisation applying for grant funding wants to see all prospecting and contract activity in one place, including a summary of the status of their efforts. Some of this data is in custom fields, and some is calculated (estimated worth) and added to the Activity.get API via a custom API wrapper.

## Usage

After installation, visit **Administer** » **Customise Data and Screens** » **Activity Tabs** to configure.

This screen lists the activity tabs you have defined and lets you edit/add/delete them:

![Screenshot](/images/screenshot-list.png)

Adding/editing an activity tab is fairly simple:

![Screenshot](/images/screenshot-config.png)

- The Tab Name is what appears on the Contact Record.

- Activity Types - you can select one or more types to include.

- Columns - you can add as many or as few columns as you want, including custom data. You can change the order, too.

- Remember to hit save 😉

Then view a contact's record:

![Screenshot](/images/screenshot-tab.png)


## Known Issues

This is in early development, and there could be lots of ways to improve it. Feel free to add an issue, and [get in touch](https://artfulrobot.uk/contact) if you'd like to fund a feature. PRs welcome too!
