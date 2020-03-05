import React, { useState } from 'react';
import styles from './Tabs.css';

function Tabs({children}) {
    const [selected, select] = useState(0);
    return (
        <div className={styles.tabsWrapper}>
            <div className={styles.tabsHeader}>
                {
                    children.map((elem, index) => {
                           let className = index === selected ? styles.selectedTab : '';
                           return (
                               <div onClick={() => select(index)} className={className} key={index}>
                                   {elem.props.title}
                               </div>
                           )
                    })
                }
            </div>
            <div className={styles.tabsContent}>
                {
                    children[selected]
                }
            </div>
        </div>
    )
}
export default Tabs;