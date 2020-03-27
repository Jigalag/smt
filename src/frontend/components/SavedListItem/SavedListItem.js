import React, { useState, useEffect } from 'react';
import styles from './SavedListItem.css';
import Input from "../Input/Input";

function SavedListItem({item, changePosition, removePost}) {
    const [position, setPosition] = useState(0);
    useEffect(() => {
        setPosition(item.position)
    }, [item]);
    return (
        <div className={styles.listItem}>
            <div className={styles.deleteButton}>
                <button  className={styles.button} onClick={(e) => removePost(e, item)}>
                    Delete Post
                </button>
            </div>
            {
                item.image &&
                <div className={styles.listImageWrapper}>
                    <img className={styles.listImage}
                         src={item.image} alt=""/>
                </div>
            }

            {
                <div className={styles.listContent}>
                    <div className={styles.listText}
                         dangerouslySetInnerHTML={{ __html: item.post_content }} />
                         <form className={styles.form}>
                             <div className={styles.input}>
                                 <Input type={'number'} value={position} onChange={setPosition} label={'Position of post'}/>
                             </div>
                             <div className={styles.submit}>
                                 <Input type={'submit'} click={ (e) => changePosition(e, position, item) } />
                             </div>
                         </form>
                </div>
            }
        </div>
    )
}
export default SavedListItem;