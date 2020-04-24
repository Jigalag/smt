import React, { useEffect, useState } from 'react';
import styles from "./App.css"
import Header from "../Header/Header";
import Tabs from "../Tabs/Tabs";
import Tab from "../Tab/Tab";
import Title from "../Title/Title";
import Twitter from "../Twitter/Twitter";
import Settings from "../Settings/Settings";
import SavedList from "../SavedList/SavedList";
import Facebook from "../Facebook/Facebook";
import Modal from "../Modal/Modal";

function App() {
    const [settings, setSettings] = useState({});
    const [disabledTw, setDisabledTw] = useState(true);
    const [disabledFb, setDisabledFb] = useState(true);
    const [FBToken, setFBToken] = useState(true);
    const [openingTab, setOpeningTab] = useState({});
    const [currentTab, setCurrentTab] = useState('');
    const [postsIsSaved, setIsSavedPosts] = useState(false);
    const [forcePosts, setForcePosts] = useState(false);
    const [forceSettings, setForceSettings] = useState(false);
    const [disabledPublish, setDisabledPublish] = useState(true);
    const [savedPosts, setSavedPosts] = useState([]);
    const [savedPostIds, setSavedPostIds] = useState([]);
    const [maxPostsNumber, setMaxPostsNumber] = useState(0);
    const [checkedPosts, setCheckedPosts] = useState([]);
    const [isModal, setIsModal] = useState(false);
    const [publishSuccess, setPublishSuccess] = useState('');
    const [publishError, setPublishError] = useState('');

    const isDisabledCheckbox = (post) => {
        const checkedPostIds = checkedPosts.map((item) => {
            return item.id;
        });
        const originalPostsIds = savedPosts.map((item) => {
            return item.originalId;
        });
        return (checkedPosts.length >= maxPostsNumber && !checkedPostIds.includes(post.id))
            || savedPosts.length >= maxPostsNumber || originalPostsIds.includes(post.id_str) ||
            ((checkedPosts.length + savedPosts.length) >= maxPostsNumber && !checkedPostIds.includes(post.id));
    };

    const getForceSettings = () => {
        setForceSettings(!forceSettings);
    };

    const savePosts = (type) => {
        const posts = [...checkedPosts];
        let action = '';
        switch (type) {
            case 'twitter':
                action = 'saveTwitterPosts';
                break;
            case 'facebook':
                action = 'saveFacebookPosts';
                break;
            default:
                break;
        }
        return new Promise((resolve) => {
            fetch(window.ajaxURL + `?action=${action}`, {
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                method: 'POST',
                body: JSON.stringify(posts),
            }).then(response => response.json())
                .then(result => {
                    resolve();
                    setIsSavedPosts(!postsIsSaved);
                    setCheckedPosts([]);
                });
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
        console.log(currentCheckedPosts);
        setCheckedPosts(currentCheckedPosts);
    };

    const showModal = (index, select) => {
        setIsModal(true);
        setOpeningTab({index, select});
    };

    const submitModal = () => {
        savePosts(currentTab).then(() => {
            setIsModal(false);
            setOpeningTab({});
            openingTab.select(openingTab.index);
        });
    };

    const cancelModal = () => {
        setCheckedPosts([]);
        setIsModal(false);
        openingTab.select(openingTab.index);
    };

    const publishPosts = () => {
        fetch(window.ajaxURL + '?action=publishPosts')
            .then(response => response.json())
            .then(result => {
                setPublishSuccess(result.data);
                setTimeout(() => {
                    setPublishSuccess('');
                }, 5000);
            });
    };

    useEffect(() => {
        const getData = async () => {
            const response = await fetch(window.ajaxURL + '?action=getSMTSettings');
            const content = await response.json();
            setSettings(content);
            setFBToken(content.facebook.token);
            setMaxPostsNumber(content.general.numberPosts);
            if (content.twitter.token) {
                setDisabledTw(false);
            }
            if (content.facebook.token) {
                setDisabledFb(false);
            }
        };
        getData();
    }, [forceSettings]);

    useEffect(() => {
        const getSavedPosts = async () => {
            const response = await fetch(window.ajaxURL + '?action=getSavedPosts');
            const content = await response.json();
            setSavedPosts(content.data);
            const updateIds = [];
            content.data.forEach(item => {
                updateIds.push(item.originalId);
            });
            setSavedPostIds(updateIds);
        };
        getSavedPosts()
    }, [postsIsSaved]);
    useEffect(() => {
        if (savedPosts.length < (maxPostsNumber * 1)) {
            setPublishError(`Saved posts count are less than ${maxPostsNumber}`);
            setDisabledPublish(true);
        } else {
            setPublishError('');
            setDisabledPublish(false);
        }
    }, [forceSettings, savedPosts, checkedPosts, maxPostsNumber]);
    return (
        <div>
            <Header />
            <div className={styles.mainWrapper}>
                <div className={styles.mainSide}>
                    <Tabs showModal={showModal}
                          checkedPosts={checkedPosts}
                          setCurrentTab={setCurrentTab}>
                        <Tab title={'Settings'}>
                            <Settings settings={settings} forceSettings={getForceSettings}/>
                        </Tab>
                        <Tab title={'Twitter'}
                             disabled={disabledTw}
                             postType={'twitter'}>
                            <div className={styles.savePostsButton}>
                                <button className={styles.saveButton} disabled={checkedPosts.length === 0} onClick={() => savePosts('twitter')}>
                                    Add Posts
                                </button>
                            </div>
                            <Twitter
                                forcePosts={forcePosts}
                                savedPostIds={savedPostIds}
                                isDisabledCheckbox={isDisabledCheckbox}
                                checkPost={checkPost}
                            />
                        </Tab>
                        <Tab title={'Facebook'}
                             disabled={disabledFb}
                             postType={'facebook'}>
                            <div className={styles.savePostsButton}>
                                <button className={styles.saveButton} disabled={checkedPosts.length === 0} onClick={() => savePosts('facebook')}>
                                    Add Posts
                                </button>
                            </div>
                            <Facebook
                                token={FBToken}
                                forcePosts={forcePosts}
                                savedPostIds={savedPostIds}
                                isDisabledCheckbox={isDisabledCheckbox}
                                checkPost={checkPost}
                                showModal={showModal}
                            />
                        </Tab>
                    </Tabs>
                </div>
                <div className={styles.savedSide}>
                    <Title text={'Saved Posts'}
                           disabledPublish={disabledPublish}
                           publishError={publishError}
                           publishSuccess={publishSuccess}
                           publishPosts={publishPosts}/>
                    <SavedList listArray={savedPosts} changePosition={changePosition} removePost={removePost} maxPostsNumber={maxPostsNumber}/>
                </div>
            </div>
            {
                isModal && (
                    <Modal
                        openingTab={openingTab}
                        title={'Confirm checked posts'}
                        content={'Please save checked posts before change social network. Press \'Cancel\' to reset checked posts.'}
                        submit={submitModal}
                        cancel={cancelModal}
                    />
                )
            }
        </div>
    )
}
export default App;
