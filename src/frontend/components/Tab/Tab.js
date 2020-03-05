import React from 'react';
import styles from './Tab.css';

function Tab({children}) {
    return (
        <div className={styles.tab}>
            {
                children
            }
        </div>
    )
}
export default Tab;