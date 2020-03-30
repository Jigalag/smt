import React, { useEffect, useState } from 'react';
import styles from "./App.css"
import Header from "../Header/Header";
import Tabs from "../Tabs/Tabs";
import Tab from "../Tab/Tab";
import Title from "../Title/Title";
import Twitter from "../Twitter/Twitter";
import Settings from "../Settings/Settings";
import SavedList from "../SavedList/SavedList";

function App() {
    const [settings, setSettings] = useState({});
    const [disabled, setDisabled] = useState(true);
    const [postsIsSaved, setIsSavedPosts] = useState(false);
    const [forcePosts, setForcePosts] = useState(false);
    const [savedPosts, setSavedPosts] = useState([]);
    const [savedPostIds, setSavedPostIds] = useState([]);
    const [maxPostsNumber, setMaxPostsNumber] = useState(0);
    const [checkedPosts, setCheckedPosts] = useState([]);

    const isDisabledCheckbox = (post) => {
        const checkedPostIds = checkedPosts.map((item) => {
            return item.id;
        });
        const originalPostsIds = savedPosts.map((item) => {
            return item.originalId;
        });
        return (checkedPosts.length >= maxPostsNumber && !checkedPostIds.includes(post.id))
            || savedPosts.length >= maxPostsNumber || originalPostsIds.includes(post.id_str) ||
            (checkedPosts.length + savedPosts.length) >= maxPostsNumber;
    };

    const savePosts = () => {
        const posts = [...checkedPosts];
        fetch(window.ajaxURL + '?action=saveTwitterPosts', {
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            method: 'POST',
            body: JSON.stringify(posts),
        }).then(response => response.json())
            .then(result => {
                setIsSavedPosts(!postsIsSaved);
                setCheckedPosts([]);
            });
    };

    const removePost = (e, post) => {
        e.preventDefault();
        const data = {
            'postId': post.ID
        };
        fetch(window.ajaxURL + '?action=removePost', {
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            method: 'POST',
            body: JSON.stringify(data),
        }).then(response => response.json())
            .then(result => {
                const updateIds = savedPostIds.filter(item => {
                    return item !== post.originalId;
                });
                setSavedPostIds(updateIds);
                setIsSavedPosts(!postsIsSaved);
                setForcePosts(!forcePosts);
            });
    };

    const changePosition = (e, position, post) => {
        e.preventDefault();
        const data = {
            'postId': post.ID,
            'position': position*1,
        };
        fetch(window.ajaxURL + '?action=updatePosition', {
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            method: 'POST',
            body: JSON.stringify(data),
        }).then(response => response.json())
            .then(result => {
                setIsSavedPosts(!postsIsSaved);
            });
    };

    const checkPost = (post) => {
        const currentCheckedPosts = [...checkedPosts];
        const checkedPostIds = currentCheckedPosts.map((item) => {
            return item.id;
        });
        if (checkedPostIds.includes(post.id)) {
            const index = currentCheckedPosts.findIndex((item) => {
                return item.id === post.id;
            });
            currentCheckedPosts.splice(index, 1);
        } else {
            currentCheckedPosts.push(post);
        }
        setCheckedPosts(currentCheckedPosts);
    };

    useEffect(() => {
        const getData = async () => {
            const response = await fetch(window.ajaxURL + '?action=getSMTSettings');
            const content = await response.json();
            setSettings(content);
            setMaxPostsNumber(content.general.numberPosts);
            if (content.twitter.token) {
                setDisabled(false);
            }
        };
        getData();
    }, []);

    useEffect(() => {
        const getSavedPosts = async () => {
            const response = await fetch(window.ajaxURL + '?action=getSavedPosts');
            const content = await response.json();
            setSavedPosts(content.data);
            const updateIds = [];
            content.data.forEach(item => {
                updateIds.push(item.originalId);
            });
            setSavedPostIds(updateIds)
        };
        getSavedPosts()
    }, [postsIsSaved]);
    return (
        <div>
            <Header />
            <div className={styles.mainWrapper}>
                <div className={styles.mainSide}>
                    <Tabs>
                        <Tab title={'Settings'}>
                            <Settings settings={settings}/>
                        </Tab>
                        <Tab title={'Twitter'} disabled={disabled}>
                            <div className={styles.savePostsButton}>
                                <button className={styles.saveButton} disabled={checkedPosts.length === 0} onClick={() => savePosts()}>
                                    Save Posts
                                </button>
                            </div>
                            <Twitter
                                forcePosts={forcePosts}
                                savedPostIds={savedPostIds}
                                isDisabledCheckbox={isDisabledCheckbox}
                                checkPost={checkPost}
                            />
                        </Tab>
                    </Tabs>
                </div>
                <div className={styles.savedSide}>
                    <Title text={'Saved Posts'}/>
                    <SavedList listArray={savedPosts} changePosition={changePosition} removePost={removePost} maxPostsNumber={maxPostsNumber}/>
                </div>
            </div>
        </div>
    )
}
export default App;