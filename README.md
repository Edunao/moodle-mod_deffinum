# DEFFINUM – Moodle Plugin
==========================

Overview
--------
mod_deffinum is a Moodle plugin derived from the SCORM module.  
Its goal is to allow external tools (virtual reality, augmented reality, 360° environments, serious games, etc.) to send tracking data directly into Moodle through WebServices.  
These data are then used in a detailed visual report, enabling teachers, trainers, and tutors to discuss learners' progress and results.

---

System Requirements
-------------------
*   Moodle 4.5
*   PHP 8.1

---

Installation
------------
1.  Download from GitHub:  
    `git clone https://github.com/Edunao/moodle-mod_deffinum.git mod/deffinum`
2.  Log in as administrator on Moodle.
3.  Run the upgrade so Moodle installs the plugin and its tables.
4.  Verify that the **mod_deffinum** activity is available when adding activities.

---

Usage
-----
*   External tools send data through the WebServices exposed by DEFFINUM.
*   Users can access reports in Moodle to analyze progress and results.

---

Update and Uninstallation
-------------------------
*   **Update**: replace the `mod/deffinum` folder and then run the Moodle upgrade.
*   **Uninstallation**: remove the activity from Moodle administration, then delete the `mod/deffinum` folder.

---

Languages
---------
*   French (fr)
*   English (en)

---

License
-------
Released under the GNU GPL v3 license.  
