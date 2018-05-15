    public function getParentIdAttribute(){
        return User::where('left_child', $this->id)
            ->orWhere('rightChild')
