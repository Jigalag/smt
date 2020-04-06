import React from 'react';
import styles from './Settings.css';
import Tabs from "../Tabs/Tabs";
import Tab from "../Tab/Tab";
import GeneralSettings from "./GeneralSettings/GeneralSettings";
import TwitterSettings from "./TwitterSettings/TwitterSettings";
import FacebookSettings from "./FacebookSettings/FacebookSettings";

function Settings({settings, forceSettings}) {
    const { general, facebook, twitter } = settings;
    return (
        <div className={styles.settings}>
            <Tabs simpleTab={true}>
                <Tab title={'General Settings'}>
                    <GeneralSettings general={general || {}}/>
                </Tab>
                <Tab title={'Twitter Settings'}>
                    <TwitterSettings forceSettings={forceSettings} twitterSettings={twitter || {}}/>
                </Tab>
                <Tab title={'Facebook Settings'}>
                    <FacebookSettings forceSettings={forceSettings} facebookSettings={facebook || {}}/>
                </Tab>
            </Tabs>
        </div>
    )
}
export default Settings;