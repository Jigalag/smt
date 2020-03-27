import React from 'react';
import styles from './ListItem.css';

function ListItem({item, savedPostIds, isDisabledCheckbox, checkPost}) {
    const checked = () => {
        return savedPostIds.includes(item.id_str);
    };
    return (
        <div className={styles.listItem}>
            {
                item.entities.media && item.entities.media[0] &&
                <div className={styles.listImageWrapper}>
                    <img className={styles.listImage}
                         src={item.entities.media[0].media_url} alt=""/>
                </div>
            }

            {
                <div className={styles.listContent}>
                    <div className={styles.listText}>
                        {
                            item.full_text
                        }
                    </div>
                    <div className={styles.listCheckbox}>
                        <input type="checkbox"
                               defaultChecked={checked()}
                               disabled={isDisabledCheckbox(item)}
                               onChange={() => { checkPost(item)} }
                        />
                    </div>
                </div>
            }
        </div>
    )
}
export default ListItem;