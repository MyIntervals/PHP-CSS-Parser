<?php

namespace Sabberworm\CSS\Comment;

interface Commentable
{
	/**
	 * @param Comment[] $aComments Array of comments.
	 */
	public function addComments(array $aComments);

	/**
	 * @return Comment[]
	 */
	public function getComments();

	/**
	 * @param Comment[] $aComments Array containing Comment objects.
	 */
	public function setComments(array $aComments);
}
