import React from 'react';
import styles from './Settings.css';
import Tabs from "../Tabs/Tabs";
import Tab from "../Tab/Tab";
import GeneralSettings from "./GeneralSettings/GeneralSettings";
import TwitterSettings from "./TwitterSettings/TwitterSettings";

function Settings({settings}) {
    const { general, twitter } = settings;
    return (
        <div className={styles.settings}>
            <Tabs>
                <Tab title={'General Settings'}>
                    <GeneralSettings general={general || {}}/>
                </Tab>
                <Tab title={'Twitter Settings'}>
                    <TwitterSettings twitterSettings={twitter || {}}/>
                </Tab>
            </Tabs>
        </div>
    )
}
export default Settings;