class TreeNode {
	public label = '';
	public children: TreeNode[] = [];
	public selected = false;
	public open = true;
	public icon = '';
	public data?: any = null;
	public color = 'black';
}

export default TreeNode;
