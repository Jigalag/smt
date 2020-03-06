import React, { useEffect, useState } from 'react';
import styles from "./App.css"
import Header from "../Header/Header";
import Tabs from "../Tabs/Tabs";
import Tab from "../Tab/Tab";
import Twitter from "../Twitter/Twitter";
import Settings from "../Settings/Settings";

function App() {
    const [isAuth, setAuth] = useState(false);
    return (
        <div>
            <Header />
            <div className={styles.mainWrapper}>
                <div className={styles.mainSide}>
                    <Tabs>
                        <Tab title={'Settings'}>
                            <Settings />
                        </Tab>
                        <Tab title={'Twitter'}>
                            <Twitter />
                        </Tab>
                    </Tabs>
                </div>
                <div className={styles.savedSide}>

                </div>
            </div>
        </div>
    )
}
export default App;