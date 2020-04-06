import React, { useState } from 'react';
import styles from './Tabs.css';

function Tabs({children, setCurrentTab, checkedPosts, showModal, simpleTab}) {
    const [selected, select] = useState(0);
    const changeTab = (elem, index, type) => {
        if (simpleTab) {
            !elem.props.disabled && select(index)
        } else {
            if (checkedPosts.length > 0) {
                showModal(index, select)
            } else {
                type ? setCurrentTab(type) : setCurrentTab('');
                !elem.props.disabled && select(index)
            }
        }
    };
    return (
        <div className={styles.tabsWrapper}>
            <div className={styles.tabsHeader}>
                {
                    children.map((elem, index) => {
                           let className = index === selected ? `${styles.selectedTab} ${styles.tabItem}` : styles.tabItem;
                           return (
                               <div onClick={() => changeTab(elem, index, elem.props.postType)} className={className} key={index}>
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