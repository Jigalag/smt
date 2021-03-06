import React from 'react';
import styles from './SavedList.css';
import SavedListItem from "../SavedListItem/SavedListItem";

function SavedList({listArray, changePosition, removePost, maxPostsNumber}) {
    return (
        <div className={styles.list}>
            {
                listArray.map((item, index) => (
                    <SavedListItem item={item}
                                   key={index}
                                   removePost={removePost}
                                   changePosition={changePosition}
                                   maxPostsNumber={maxPostsNumber}
                    />
                ))
            }
        </div>
    )
}
export default SavedList;