import React from 'react';
import styles from './List.css';
import ListItem from "../ListItem/ListItem";

function List({listArray, savedPostIds, isDisabledCheckbox, checkPost}) {
    return (
        <div className={styles.list}>
            {
                listArray.map((item, index) => (
                    <ListItem item={item} key={index}
                              savedPostIds={savedPostIds}
                              isDisabledCheckbox={isDisabledCheckbox}
                              checkPost={checkPost}
                    />
                ))
            }
        </div>
    )
}
export default List;