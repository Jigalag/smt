import React, { useState, useEffect } from 'react';
import styles from './SavedListItem.css';
import Input from "../Input/Input";

function SavedListItem({item, changePosition, removePost, maxPostsNumber}) {
    const [position, setPosition] = useState(0);
    const moveTop = (e) => {
        const newPosition = position > 1 ? (position*1 - 1) : 1;
        changePosition(e, newPosition, item)
    };
    const moveBottom = (e) => {
        const newPosition = position < maxPostsNumber ? (position*1 + 1) : 1;
        changePosition(e, newPosition, item)
    };
    useEffect(() => {
        setPosition(item.position)
    }, [item]);
    return (
        <div className={styles.listItem}>
            <div className={styles.deleteButton}>
                <button className={styles.button} onClick={(e) => removePost(e, item)}>
                    Delete Post
                </button>
                <div className={styles.changePositionButtons}>
                    <b>Current position: {position} </b>
                    <button className={styles.button}
                            onClick={(e) => moveTop(e)}
                            disabled={position*1 === 1}>
                        Up
                    </button>
                    <button className={styles.button}
                            onClick={(e) => moveBottom(e)}
                            disabled={position*1 === maxPostsNumber}>
                        Down
                    </button>
                </div>
            </div>
            {
                item.media_type === 'video' &&
                <div className={styles.listImageWrapper}
                     dangerouslySetInnerHTML={{ __html: item.video_template }}>
                </div>
            }
            {
                item.image && item.media_type !== 'video'&&
                <div className={styles.listImageWrapper}>
                    <img className={styles.listImage}
                         src={item.image} alt=""/>
                </div>
            }

            {
                <div className={styles.listContent}>
                    <div className={styles.listText}
                         dangerouslySetInnerHTML={{ __html: item.post_content }} />
                </div>
            }
        </div>
    )
}
export default SavedListItem;