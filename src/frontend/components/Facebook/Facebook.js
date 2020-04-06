import React, { useState, useEffect } from 'react';
import List from "../List/List";
const URL = 'https://graph.facebook.com/v6.0/lohikaSF/feed?fields=message,created_time,attachments{media},permalink_url';

function Facebook({savedPostIds, isDisabledCheckbox, checkPost, forcePosts, token}) {
    const [posts, setPosts] = useState([]);
    const [error, setError] = useState('');
    const [isError, setIsError] = useState(false);
    const [isLoading, setIsLoading] = useState(false);
    useEffect(() => {
        // setPosts([]);
        const getFacebook = async () => {
            setIsLoading(true);
            const result = await fetch(URL, {
                headers: {
                    Authorization: `Bearer ${token}`
                }
            });
            const content = await result.json();
            if (content.error) {
                setIsError(true);
                if (content.error.message) {
                    setError(content.error.message);
                }
            } else {
                const updatedContent = content.data.map((item) => {
                    const attachmentsMediaUrl = item.attachments ? (item.attachments.data && item.attachments.data[0] && item.attachments.data[0].media && item.attachments.data[0].media.image && item.attachments.data[0].media.image.src) : undefined;
                    const attachmentsMediaType = item.attachments ? (item.attachments.data && item.attachments.data[0] && item.attachments.data[0].media && item.attachments.data[0].media.source ? 'video' : 'photo') : '';
                    const attachmentsMediaSource = item.attachments ? (item.attachments.data && item.attachments.data[0] && item.attachments.data[0].media && item.attachments.data[0].media.source) : undefined;
                    const updatedItem = {
                        entities: {
                            media: []
                        },
                        extended_entities:  {
                            media: []
                        },
                        full_text: item.message,
                        created_at: item.created_time,
                        id_str: item.id,
                        id: item.id,
                        permalink_url: item.permalink_url
                    };
                    if (attachmentsMediaUrl) {
                        updatedItem.entities = {
                            media: [
                                {
                                    media_url: attachmentsMediaUrl,
                                }
                            ]
                        };
                        updatedItem.extended_entities = {
                            media: [
                                {
                                    type: attachmentsMediaType,
                                    source: attachmentsMediaSource
                                }
                            ]
                        }
                    }
                    if (attachmentsMediaSource) {
                        updatedItem.extended_entities = {
                            media: [
                                {
                                    media_url: attachmentsMediaUrl,
                                    type: attachmentsMediaType,
                                    source: attachmentsMediaSource
                                }
                            ]
                        }
                    }
                    return updatedItem;
                });
                setPosts(updatedContent);
            }
            setIsLoading(false);
        };
        getFacebook();
    }, [forcePosts]);
    if (isLoading) {
        return (
            <div>
                Loading
            </div>
        )
    }
    return (
        <div>
            {
                isError && (
                    <div>
                        {
                            error
                        }
                    </div>
                )
            }
            {
                !isError && (
                    <div>
                        <List listArray={posts}
                              savedPostIds={savedPostIds}
                              checkPost={checkPost}
                              isDisabledCheckbox={isDisabledCheckbox}
                        />
                    </div>
                )
            }
        </div>
    )
}
export default Facebook;