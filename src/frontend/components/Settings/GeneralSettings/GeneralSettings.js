import React, { useState, useEffect } from 'react';
import styles from './../Settings.css';
import Input from "../../Input/Input";

function GeneralSettings({ general }) {
    const [postNumber, setPostNumber] = useState(0);
    const [postCategoryId, setCategoryId] = useState(0);
    const [postDraftCategoryId, setDraftCategoryId] = useState(0);
    const { numberPosts = 0, categoryId = 0, draftCategoryId = 0 } = general;
    const generalSubmit = (e) => {
        e.preventDefault();
        const data = {
            'numberPosts': postNumber,
            'categoryId': postCategoryId,
            'draftCategoryId': postDraftCategoryId
        };
        fetch(window.ajaxURL + '?action=saveGeneralSettings', {
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            method: 'POST',
            body: JSON.stringify(data),
        })
    };
    useEffect(() => {
        setPostNumber(numberPosts);
        setCategoryId(categoryId);
        setDraftCategoryId(draftCategoryId);
    }, [numberPosts, categoryId, draftCategoryId]);
    return (
        <div className={styles.settings}>
            <form>
                <Input type={'number'} value={postNumber} onChange={setPostNumber} label={'Number of posts'}/>
                <Input type={'number'} value={postCategoryId} onChange={setCategoryId} label={'Trending Category ID'}/>
                <Input type={'number'} value={postDraftCategoryId} onChange={setDraftCategoryId} label={'Trending Draft Category ID'}/>
                <Input type={'submit'} click={ generalSubmit } />
            </form>
        </div>
    )
}
export default GeneralSettings;